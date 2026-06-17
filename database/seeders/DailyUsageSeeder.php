<?php

namespace Database\Seeders;

use App\Models\{Store, Ingredient, DailyUsage, DailyConfirmation, StoreStock, MutationItem, User};
use App\Services\FifoService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Mengisi pencatatan harian (daily_usages) untuk bulan berjalan dengan
 * pemakaian yang REALISTIS (kecil, tidak melebihi stok) DAN memotong stok
 * beneran lewat FIFO — supaya angka di Dashboard, Saldo Stok, dan FIFO konsisten.
 *
 * Alur:
 *   1. Hapus pencatatan bulan berjalan (idempotent).
 *   2. Recalculate FIFO + sync stock_balance → dapat baseline stok bersih.
 *   3. Susun pemakaian harian kecil (maks ~35% stok, per kemasan pack).
 *   4. Insert daily_usages + daily_confirmations.
 *   5. Recalculate FIFO + sync stock_balance lagi → stok terpotong sesuai pemakaian.
 */
class DailyUsageSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::where('email', 'admin@glacier.id')->value('id') ?? 1;

        $stores = Store::where('is_active', true)->get();
        $ingredients = Ingredient::where('type', 'raw')
            ->where('is_active', true)
            ->with(['packagings' => fn($q) => $q->where('is_active', true)])
            ->get()
            ->filter(fn($i) => $i->packagings->isNotEmpty())
            ->values();

        if ($stores->isEmpty() || $ingredients->isEmpty()) {
            $this->command?->warn('DailyUsageSeeder dilewati: toko/bahan belum ada.');
            return;
        }

        $start = Carbon::now()->startOfMonth();
        $today = Carbon::now()->startOfDay();
        $dates = [];
        for ($d = $start->copy(); $d->lte($today); $d->addDay()) {
            $dates[] = $d->copy();
        }
        $dayCount = count($dates);
        $storeIds = $stores->pluck('id');

        // ── 1. Bersihkan pencatatan bulan berjalan ────────────────────────────
        DailyUsage::whereIn('store_id', $storeIds)
            ->whereBetween('usage_date', [$start->toDateString(), $today->toDateString()])->delete();
        DailyConfirmation::whereIn('store_id', $storeIds)
            ->whereBetween('confirmation_date', [$start->toDateString(), $today->toDateString()])->delete();

        // ── 2. Baseline: recalc FIFO + sync stock_balance ─────────────────────
        foreach ($stores as $store) {
            foreach ($ingredients as $ing) {
                FifoService::recalculate($store->id, $ing->id);
                $this->syncBalance($store->id, $ing->id);
            }
        }

        // ── 3 & 4. Susun + insert pemakaian harian kecil ──────────────────────
        $usageBatch   = [];
        $confirmBatch = [];
        $touched      = []; // "store-ing" yang dipakai → perlu recalc ulang

        foreach ($stores as $store) {
            foreach ($dates as $date) {
                $confirmBatch[] = [
                    'store_id'          => $store->id,
                    'confirmation_date' => $date->toDateString(),
                    'confirmed_by'      => $adminId,
                    'created_at'        => now(), 'updated_at' => now(),
                ];
            }

            foreach ($ingredients as $ing) {
                $pkg = $ing->packagings->first();
                $ptb = (float) $pkg->pack_to_base;
                if ($ptb <= 0) continue;

                $availBase = (float) StoreStock::where('store_id', $store->id)
                    ->where('ingredient_id', $ing->id)->value('stock_balance');
                if ($availBase <= 0) continue;

                // Budget pemakaian sebulan: 8–18% stok, dikonversi ke jumlah pack
                // (kecil & wajar — supaya stok tidak langsung kritis)
                $budgetBase = $availBase * (mt_rand(8, 18) / 100);
                $maxPacks   = (int) floor($budgetBase / $ptb);
                if ($maxPacks < 1) continue; // kemasan besar (mis. sak 25kg) → tak terpakai harian

                $dailyAvg  = $maxPacks / max(1, $dayCount);
                $remaining = $maxPacks;

                foreach ($dates as $date) {
                    if ($remaining <= 0) break;
                    if (mt_rand(1, 100) <= 25) continue; // ~25% hari libur pakai

                    $cap = max(1, (int) ceil($dailyAvg * 1.2));
                    $q   = min($remaining, mt_rand(1, $cap));

                    $usageBatch[] = [
                        'store_id'     => $store->id,
                        'ingredient_id' => $ing->id,
                        'packaging_id' => $pkg->id,
                        'usage_date'   => $date->toDateString(),
                        'qty_pack'     => $q,
                        'created_by'   => $adminId,
                        'created_at'   => now(), 'updated_at' => now(),
                    ];
                    $remaining -= $q;
                    $touched[$store->id . '-' . $ing->id] = [$store->id, $ing->id];
                }
            }
        }

        foreach (array_chunk($confirmBatch, 500) as $c) DB::table('daily_confirmations')->insert($c);
        foreach (array_chunk($usageBatch, 1000) as $c) DB::table('daily_usages')->insert($c);

        // ── 5. Potong stok: recalc FIFO + sync stock_balance ──────────────────
        foreach ($touched as [$sid, $iid]) {
            FifoService::recalculate($sid, $iid);
            $this->syncBalance($sid, $iid);
        }

        $this->command?->info('DailyUsageSeeder: ' . count($usageBatch) . ' baris pemakaian di '
            . count($touched) . ' bahan/toko (stok dipotong via FIFO).');
    }

    /** Samakan store_stocks.stock_balance dengan total sisa FIFO. */
    private function syncBalance(int $storeId, int $ingredientId): void
    {
        $bal = (float) MutationItem::whereHas('mutation', fn($q) =>
                $q->where('destination_store_id', $storeId)->where('status', 'confirmed'))
            ->where('ingredient_id', $ingredientId)
            ->sum('remaining_qty');

        StoreStock::where('store_id', $storeId)
            ->where('ingredient_id', $ingredientId)
            ->update(['stock_balance' => $bal]);
    }
}
