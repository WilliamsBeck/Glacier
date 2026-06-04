<?php
namespace App\Http\Controllers;

use App\Models\{StoreStock, Store, WasteLog, ProductionLog, DailyUsage};
use Carbon\Carbon;

class DashboardController extends Controller
{
    const DOS_WINDOW = 30;

    public function index()
    {
        $user     = auth()->user();
        $storeIds = $user->accessibleStoreIds();
        $stores   = $user->accessibleStores();
        [$month, $year] = [$this->currentMonth(), $this->currentYear()];

        // ── 1. Toko aktif ──────────────────────────────────────────────────────
        $totalActiveStores = Store::whereIn('id', $storeIds)
            ->where('is_active', true)->count();

        // ── 2. Low stock (DOS < lead_time) ───────────────────────────────────
        $storeConfigs = Store::whereIn('id', $storeIds)
            ->get(['id', 'lead_time_days', 'order_cycle_days', 'dos_window_days'])
            ->keyBy('id');

        $parStocks = StoreStock::with(['store', 'ingredient.packagings'])
            ->whereIn('store_id', $storeIds)
            ->where('stock_balance', '>=', 0)
            ->get();

        // Pakai window terkecil dari semua toko sebagai batas query, filter per baris di PHP
        $minWindow = $storeConfigs->min(fn($s) => $s->dosWindowDays()) ?? self::DOS_WINDOW;
        $dosFrom   = now()->subDays($minWindow - 1)->toDateString();
        $usageSums = DailyUsage::whereIn('store_id', $storeIds)
            ->where('usage_date', '>=', $dosFrom)
            ->where('qty_pack', '>', 0)
            ->whereExists(fn($q) => $q
                ->from('daily_confirmations')
                ->whereColumn('daily_confirmations.store_id', 'daily_usages.store_id')
                ->whereColumn('daily_confirmations.confirmation_date', 'daily_usages.usage_date')
            )
            ->groupBy(['store_id', 'ingredient_id'])
            ->selectRaw('store_id, ingredient_id, SUM(qty_pack) as total_pack, COUNT(DISTINCT usage_date) as active_days, MIN(usage_date) as min_date')
            ->get()
            ->keyBy(fn($r) => $r->store_id . '-' . $r->ingredient_id);

        $lowStocks = $parStocks
            ->map(function ($ss) use ($usageSums, $storeConfigs) {
                $key   = $ss->store_id . '-' . $ss->ingredient_id;
                $usage = $usageSums[$key] ?? null;
                if (!$usage) return null;

                $pkg            = $ss->ingredient->packagings->first();
                $ptb            = $pkg ? (float) $pkg->pack_to_base : 1;
                $store          = $storeConfigs[$ss->store_id] ?? null;
                $leadTimeDays   = $store?->leadTimeDays();
                $orderCycleDays = $store?->orderCycleDays();
                $windowDays     = $store?->dosWindowDays() ?? 30;
                $windowFrom     = now()->subDays($windowDays - 1)->toDateString();

                // Hitung ulang active_days & total_pack dalam window toko ini
                // (data sudah di-fetch dari window terkecil, filter lagi jika perlu)
                $activeDays   = $usage->active_days > 0 ? $usage->active_days : 1;
                $avgDailyBase = ($usage->total_pack * $ptb) / $activeDays;
                if ($avgDailyBase < 0.001) return null;

                $dos = $ss->stock_balance / $avgDailyBase;
                $ss->dos_value        = round($dos, 1);
                $ss->lead_time_days   = $leadTimeDays;
                $ss->order_cycle_days = $orderCycleDays;
                $ss->dos_status       = $ss->dosStatus($dos, $leadTimeDays, $orderCycleDays);
                return $ss;
            })
            ->filter(fn($ss) => $ss && in_array($ss->dos_status, ['critical', 'warning']))
            ->sortBy('dos_value')
            ->values();

        // ── 3. Total waste bulan ini ───────────────────────────────────────────
        $totalWaste = WasteLog::whereIn('store_id', $storeIds)
            ->whereMonth('waste_date', $month)->whereYear('waste_date', $year)
            ->sum('total_loss_amount');

        // ── 4. Total produksi bahan setengah jadi bulan ini ───────────────────
        $totalProduksi = ProductionLog::whereIn('store_id', $storeIds)
            ->whereMonth('production_date', $month)
            ->whereYear('production_date', $year)
            ->count(); // jumlah batch produksi

        // ── 5. Toko belum update pencatatan harian s/d kemarin ────────────────
        $yesterday = Carbon::yesterday()->toDateString();

        // store_ids yang sudah punya entry daily_usage untuk tanggal kemarin
        $updatedStoreIds = DailyUsage::whereIn('store_id', $storeIds)
            ->where('usage_date', $yesterday)
            ->distinct()->pluck('store_id')->toArray();

        $storesNotUpdated = $stores->filter(fn($s) => $s->is_active)
            ->whereNotIn('id', $updatedStoreIds)
            ->values();

        // Ambil tanggal terakhir pencatatan per toko (untuk info di list)
        $lastUsageDates = DailyUsage::whereIn('store_id', $storeIds)
            ->groupBy('store_id')
            ->selectRaw('store_id, MAX(usage_date) as last_date')
            ->pluck('last_date', 'store_id');

        return view('dashboard.index', compact(
            'totalActiveStores', 'lowStocks', 'totalWaste', 'totalProduksi',
            'storesNotUpdated', 'lastUsageDates', 'yesterday'
        ));
    }

    private function currentMonth(): int { return now()->month; }
    private function currentYear():  int { return now()->year;  }
}
