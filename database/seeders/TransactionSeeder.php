<?php

namespace Database\Seeders;

use App\Models\{Ingredient, IngredientPackaging, Mutation, MonthlyRevenue, Opname, ProductionLog, Store, Supplier, User, WasteLog};
use App\Services\{FifoService, MutationService, StockLedgerService};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{Auth, DB};

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'super_admin')->first();
        Auth::loginUsingId($admin->id);

        $this->purchases($admin);
        $this->productions($admin);
        $this->wastes($admin);
        $this->opnames($admin);
        $this->revenues($admin);

        Auth::logout();
        $this->command->info('Transactional data seeded.');
    }

    /** 2 pembelian Zhisheng per store di bulan ini (tgl 5 & 20). */
    private function purchases(User $admin): void
    {
        $zhi = Supplier::where('type', 'zhisheng')->first();
        if (! $zhi) return;

        // [ingredient_name, qty_pack, price_per_base]
        $items = [
            ['Susu Cair UHT',   20, 13],
            ['Gula Pasir',       3, 15],
            ['Tepung Es Krim',   2, 82],
            ['Bubuk Coklat',     2, 125],
            ['Teh Hitam',        4, 62],
            ['Teh Hijau',        4, 72],
            ['Sirup Lemon',      6, 36],
            ['Sirup Strawberry', 6, 36],
            ['Selai Blueberry',  4, 58],
            ['Cup 16oz',         8, 620],
            ['Tutup Cup 16oz',   8, 210],
            ['Sedotan Jumbo',    4, 105],
        ];

        $ingredients = Ingredient::with('packagings')->get()->keyBy('name');

        foreach (Store::all() as $store) {
            foreach ([5, 20] as $day) {
                $date = now()->startOfMonth()->addDays($day - 1)->toDateString();

                // skip kalau sudah ada untuk tanggal & store ini
                $dup = Mutation::where('destination_store_id', $store->id)
                    ->where('type', 'purchase_zhisheng')
                    ->whereDate('delivery_date', $date)->exists();
                if ($dup) continue;

                $mutation = Mutation::create([
                    'type'                 => 'purchase_zhisheng',
                    'destination_store_id' => $store->id,
                    'supplier_id'          => $zhi->id,
                    'invoice_no'           => 'INV-' . $store->store_code . '-' . str_replace('-', '', $date),
                    'transaction_date'     => $date,
                    'delivery_date'        => $date,
                    'status'               => 'draft',
                    'created_by'           => $admin->id,
                ]);

                foreach ($items as [$name, $qtyPack, $price]) {
                    $ing = $ingredients[$name] ?? null;
                    if (! $ing) continue;
                    $pkg = $ing->packagings->firstWhere('supplier_id', $zhi->id) ?? $ing->packagings->first();
                    if (! $pkg) continue;

                    $totalBase = $qtyPack * (float) $pkg->pack_to_base;

                    $mutation->items()->create([
                        'ingredient_id'  => $ing->id,
                        'packaging_id'   => $pkg->id,
                        'qty_pack'       => $qtyPack,
                        'total_in_base'  => $totalBase,
                        'price_per_base' => $price,
                        'cost_subtotal'  => $totalBase * $price,
                        'remaining_qty'  => $totalBase,
                    ]);
                }

                MutationService::confirm($mutation);
            }
        }
    }

    /** 4 produksi per store dalam bulan ini. */
    private function productions(User $admin): void
    {
        $semis = Ingredient::with('compositions.child')
            ->where('type', 'semi_finished')->get();

        foreach (Store::all() as $store) {
            foreach ([3, 10, 17, 24] as $day) {
                $date = now()->startOfMonth()->addDays($day - 1)->toDateString();

                foreach ($semis as $semi) {
                    if ($semi->compositions->isEmpty()) continue;

                    $dup = ProductionLog::where('store_id', $store->id)
                        ->where('semi_finished_id', $semi->id)
                        ->whereDate('production_date', $date)->exists();
                    if ($dup) continue;

                    $qtyProduced = random_int(2000, 5000); // base unit (gram/ml)

                    $log = ProductionLog::create([
                        'store_id'         => $store->id,
                        'semi_finished_id' => $semi->id,
                        'qty_produced'     => $qtyProduced,
                        'production_date'  => $date,
                        'notes'            => 'Produksi rutin',
                        'created_by'       => $admin->id,
                    ]);

                    foreach ($semi->compositions as $comp) {
                        $qtyNeeded    = (float) $comp->qty_needed * $qtyProduced;
                        $cost         = FifoService::getCost($store->id, $comp->child_id, $qtyNeeded);
                        $pricePerBase = $qtyNeeded > 0 ? $cost / $qtyNeeded : 0;

                        $log->items()->create([
                            'raw_ingredient_id' => $comp->child_id,
                            'qty_consumed'      => $qtyNeeded,
                            'price_per_base'    => $pricePerBase,
                        ]);
                    }
                }
            }
        }
    }

    /** 2 waste per store dalam bulan ini, item kecil. */
    private function wastes(User $admin): void
    {
        $candidates = ['Susu Cair UHT', 'Gula Pasir', 'Cup 16oz', 'Sedotan Jumbo'];
        $ings = Ingredient::with('packagings')->whereIn('name', $candidates)->get();

        foreach (Store::all() as $store) {
            foreach ([8, 22] as $day) {
                $date = now()->startOfMonth()->addDays($day - 1)->toDateString();
                if (Opname::isDateLocked($store->id, $date)) continue;

                $dup = WasteLog::where('store_id', $store->id)->whereDate('waste_date', $date)->exists();
                if ($dup) continue;

                $log = WasteLog::create([
                    'store_id'    => $store->id,
                    'waste_date'  => $date,
                    'notes'       => 'Waste rutin (seeder)',
                    'recorded_by' => $admin->id,
                ]);

                $totalLoss = 0;

                foreach ($ings as $ing) {
                    $pkg = $ing->packagings->first();
                    if (! $pkg) continue;
                    $ptb = (float) $pkg->pack_to_base;

                    // 1 pack waste
                    $stockBase = $ptb;
                    $price     = FifoService::getCost($store->id, $ing->id, $stockBase, $pkg->id) / max($stockBase, 0.0001);
                    $subtotal  = $stockBase * $price;

                    $log->items()->create([
                        'source_type'          => 'raw',
                        'source_ingredient_id' => $ing->id,
                        'source_qty'           => 1,
                        'packaging_id'         => $pkg->id,
                        'qty_crate'            => 0,
                        'qty_pack'             => 1,
                        'ingredient_id'        => $ing->id,
                        'qty_base'             => $stockBase,
                        'price_per_base'       => $price,
                        'subtotal_loss'        => $subtotal,
                        'is_rework'            => false,
                    ]);

                    StockLedgerService::record(
                        $store->id, $ing->id, $date, 'waste',
                        -$stockBase, 'WasteLog', $log->id, 'Seeder waste'
                    );
                    FifoService::deduct($store->id, $ing->id, $stockBase, $pkg->id);
                    $totalLoss += $subtotal;
                }

                $log->update(['total_loss_amount' => $totalLoss]);
            }
        }
    }

    /** Opname end_month untuk 3 bulan terakhir (termasuk bulan ini), no variance. */
    private function opnames(User $admin): void
    {
        $now = now();

        foreach (Store::all() as $store) {
            for ($i = 0; $i < 3; $i++) {
                $period = $now->copy()->subMonthsNoOverflow($i);
                $opDate = $period->copy()->endOfMonth()->toDateString();
                $month  = (int) $period->format('m');
                $year   = (int) $period->format('Y');

                $dup = Opname::where('store_id', $store->id)
                    ->where('period_month', $month)->where('period_year', $year)
                    ->where('period_type', 'end_month')->exists();
                if ($dup) continue;

                $opname = Opname::create([
                    'store_id'     => $store->id,
                    'opname_date'  => $opDate,
                    'period_month' => $month,
                    'period_year'  => $year,
                    'period_type'  => 'end_month',
                    'status'       => 'approved',
                    'performed_by' => $admin->id,
                    'approved_by'  => $admin->id,
                    'notes'        => 'Seeder opname (no variance)',
                ]);

                $ingredientIds = DB::table('store_stocks')
                    ->where('store_id', $store->id)
                    ->pluck('ingredient_id');

                foreach ($ingredientIds as $ingId) {
                    $systemQty = StockLedgerService::getBalanceAt($store->id, $ingId, $opDate);
                    $pkg       = IngredientPackaging::where('ingredient_id', $ingId)->first();

                    $opname->items()->create([
                        'ingredient_id'  => $ingId,
                        'packaging_id'   => $pkg?->id,
                        'system_qty'     => $systemQty,
                        'physical_crate' => null,
                        'physical_pack'  => null,
                        'physical_base'  => $systemQty,
                        'physical_qty'   => $systemQty,
                        'variance'       => 0,
                        'price_per_base' => FifoService::getAvgPrice($store->id, $ingId),
                    ]);
                }
            }
        }
    }

    /** Omset 3 bulan terakhir, 2 periode/bulan, per toko. */
    private function revenues(User $admin): void
    {
        $now = now();
        foreach (Store::all() as $store) {
            for ($i = 0; $i < 3; $i++) {
                $d = $now->copy()->subMonthsNoOverflow($i);
                MonthlyRevenue::updateOrCreate(
                    [
                        'store_id' => $store->id,
                        'month'    => (int) $d->format('m'),
                        'year'     => (int) $d->format('Y'),
                    ],
                    [
                        'period_type'   => 'end_month',
                        'total_revenue' => random_int(40_000_000, 90_000_000),
                        'recorded_by'   => $admin->id,
                    ]
                );
            }
        }
    }
}
