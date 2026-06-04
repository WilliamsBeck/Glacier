<?php
namespace App\Http\Controllers;

use App\Models\{DailyUsage, Ingredient, IngredientCategory, Opname, OpnameItem, Store, StoreStock};
use Carbon\Carbon;
use Illuminate\Http\Request;

class OrderPlanningController extends Controller
{
    public function index(Request $request)
    {
        $stores   = auth()->user()->accessibleStores();
        $storeIds = auth()->user()->accessibleStoreIds();
        $defaultRef = now()->subMonth();

        // Daftar opname approved per toko (untuk dropdown sumber stok)
        $opnamesByStore = Opname::whereIn('store_id', $storeIds)
            ->where('status', 'approved')
            ->orderByDesc('opname_date')
            ->get(['id', 'store_id', 'opname_date', 'period_month', 'period_year', 'period_type'])
            ->groupBy('store_id');

        $storeConfigs = $stores->mapWithKeys(fn($s) => [$s->id => [
            'lead_time_days'   => $s->lead_time_days   ? (int)$s->lead_time_days   : null,
            'order_cycle_days' => $s->order_cycle_days ? (int)$s->order_cycle_days : null,
            'dos_window_days'  => $s->dosWindowDays(),
        ]]);

        // Belum pilih toko ATAU form belum lengkap → tampilkan form kosong
        if (!$request->filled('store_id') || !$request->filled('ref_date') || !$request->filled('next_order_date')) {
            return view('order-planning.index', [
                'stores'          => $stores,
                'storeConfigs'    => $storeConfigs,
                'opnamesByStore'  => $opnamesByStore,
                'tableData'       => false,
                'defaultMonth'    => $defaultRef->month,
                'defaultYear'     => $defaultRef->year,
            ]);
        }

        $request->validate([
            'store_id'        => 'required|exists:stores,id',
            'order_month'     => 'required|integer|between:1,12',
            'order_year'      => 'required|integer|min:2020',
            'ref_date'        => 'required|date',
            'next_order_date' => 'required|date|after_or_equal:ref_date',
            'ref_month'       => 'required|integer|between:1,12',
            'ref_year'        => 'required|integer|min:2020',
            'buffer_pct'      => 'nullable|numeric|min:0|max:100',
            'split_order'     => 'nullable|boolean',
            'stock_source'    => 'nullable|in:fifo,opname',
            'opname_id'       => 'nullable|exists:opnames,id',
        ]);

        // Derive: hari kebutuhan = arrival_next - ref_date
        // delivery_date  = ref_date (titik awal stok dipakai)
        // coverage_end   = next_order_date + lead_time (= tgl tiba pesanan berikutnya)
        $store     = Store::find($request->store_id);
        $leadTime  = $store?->leadTimeDays() ?? 0;
        $nextOrder = Carbon::parse($request->next_order_date);
        $arrival   = $nextOrder->copy()->addDays($leadTime);

        $request->merge([
            'delivery_date' => $request->ref_date,
            'coverage_end'  => $arrival->toDateString(),
        ]);

        $storeId = (int)$request->store_id;
        abort_unless(in_array($storeId, $storeIds), 403);

        $data = $this->buildTableData($request, $storeId);

        return view('order-planning.index', array_merge($data, [
            'stores'         => $stores,
            'storeConfigs'   => $storeConfigs,
            'opnamesByStore' => $opnamesByStore,
            'defaultMonth'   => $data['refMonth'],
            'defaultYear'    => $data['refYear'],
        ]));
    }

    // ── Export sebagai .xls ───────────────────────────────────────────────────
    public function export(Request $request)
    {
        $storeId = (int)$request->store_id;
        abort_unless(in_array($storeId, auth()->user()->accessibleStoreIds()), 403);

        // Derive: hari kebutuhan = arrival_next - ref_date
        if ($request->filled('ref_date') && $request->filled('next_order_date')) {
            $store     = Store::find($storeId);
            $leadTime  = $store?->leadTimeDays() ?? 0;
            $arrival   = Carbon::parse($request->next_order_date)->addDays($leadTime);
            $request->merge([
                'delivery_date' => $request->ref_date,
                'coverage_end'  => $arrival->toDateString(),
            ]);
        }

        $data = $this->buildTableData($request, $storeId);

        if (empty($data['tableData'])) {
            return back()->with('error', 'Tidak ada data untuk diekspor.');
        }

        $filename = 'rencana-order-' . $data['store']->name . '-' . now()->format('Ymd') . '.xls';

        return response()
            ->view('order-planning.export', $data)
            ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    // ── Core kalkulasi ────────────────────────────────────────────────────────
    private function buildTableData(Request $request, int $storeId): array
    {
        $store        = Store::find($storeId);
        $orderDate    = $request->filled('order_date') ? Carbon::parse($request->order_date) : null;
        $deliveryDate = Carbon::parse($request->delivery_date);
        $coverageEnd  = Carbon::parse($request->coverage_end);
        $refMonth     = (int)$request->ref_month;
        $refYear      = (int)$request->ref_year;
        $bufferPct    = (float)($request->buffer_pct ?? 0);
        $splitOrder   = $request->boolean('split_order');
        $stockSource  = $request->input('stock_source', 'fifo');
        $opnameId     = $request->filled('opname_id') ? (int)$request->opname_id : null;
        $daysToCover  = $deliveryDate->diffInDays($coverageEnd);

        // Lead time (informasi saja, tidak mempengaruhi kalkulasi)
        $leadTimeDays = $orderDate ? $orderDate->diffInDays($deliveryDate) : null;

        // Tanggal estimasi Order 2 — tengah coverage
        $splitDate = $splitOrder
            ? $deliveryDate->copy()->addDays((int)ceil($daysToCover / 2))
            : null;

        // ── Sumber stok saat ini ──────────────────────────────────────────────
        $selectedOpname = null;
        if ($stockSource === 'opname' && $opnameId) {
            $selectedOpname = Opname::find($opnameId);
            // physical_qty = base unit dari opname fisik
            $stockMap = OpnameItem::where('opname_id', $opnameId)
                ->get(['ingredient_id', 'physical_qty'])
                ->pluck('physical_qty', 'ingredient_id')
                ->map(fn($v) => (float)$v);
        } else {
            // Default: FIFO (saldo berjalan dari store_stocks)
            $stockSource = 'fifo';
            $stockMap = StoreStock::where('store_id', $storeId)
                ->pluck('stock_balance', 'ingredient_id')
                ->map(fn($v) => (float)$v);
        }

        // ── Referensi konsumsi ────────────────────────────────────────────────
        $refStart  = Carbon::create($refYear, $refMonth, 1)->toDateString();
        $refEnd    = Carbon::create($refYear, $refMonth, 1)->endOfMonth()->toDateString();
        $daysInRef = Carbon::create($refYear, $refMonth, 1)->daysInMonth;

        $usageSums = DailyUsage::where('store_id', $storeId)
            ->whereBetween('usage_date', [$refStart, $refEnd])
            ->where('qty_pack', '>', 0)
            ->groupBy('ingredient_id')
            ->selectRaw('ingredient_id, SUM(qty_pack) as total_pack, COUNT(DISTINCT usage_date) as active_days')
            ->get()
            ->keyBy('ingredient_id');

        if ($usageSums->isEmpty()) {
            return compact('store', 'orderDate', 'deliveryDate', 'coverageEnd', 'daysToCover',
                'leadTimeDays', 'refMonth', 'refYear', 'daysInRef', 'bufferPct',
                'splitOrder', 'splitDate', 'stockSource', 'selectedOpname')
                + ['tableData' => [], 'message' => 'Tidak ada data konsumsi untuk bulan referensi yang dipilih.'];
        }

        $categoryOrder = IngredientCategory::orderedNames();
        $ingredients   = Ingredient::with(['packagings' => fn($q) => $q->where('is_active', true)->orderBy('id')])
            ->whereIn('id', $usageSums->keys())
            ->where('type', '!=', 'semi_finished')
            ->get()
            ->sort(function ($a, $b) use ($categoryOrder) {
                $ai = array_search($a->type, $categoryOrder); $ai = $ai === false ? 99 : $ai;
                $bi = array_search($b->type, $categoryOrder); $bi = $bi === false ? 99 : $bi;
                return $ai !== $bi ? $ai - $bi : strcmp($a->name, $b->name);
            })
            ->values();

        $tableData = [];

        foreach ($ingredients as $ing) {
            $usage = $usageSums[$ing->id];
            $pkg   = $ing->packagings->first();
            if (!$pkg || $pkg->crate_to_pack <= 0 || $pkg->pack_to_base <= 0) continue;

            $crateToBase  = $pkg->crate_to_pack * $pkg->pack_to_base;
            $avgDailyPack = $usage->total_pack / $daysInRef;
            $stockBase    = $stockMap[$ing->id] ?? 0;
            $stockPack    = $stockBase / $pkg->pack_to_base;
            $stockDus     = $stockBase / $crateToBase;

            $grossPack    = $avgDailyPack * $daysToCover;
            $grossWithBuf = $grossPack * (1 + $bufferPct / 100);
            $netPack      = max(0, $grossWithBuf - $stockPack);
            $netDus       = (int) ceil($netPack / $pkg->crate_to_pack);

            $order1Dus = $splitOrder ? (int) ceil($netDus / 2) : $netDus;
            $order2Dus = $splitOrder ? (int) ceil($netDus / 2) : 0;

            $tableData[] = (object)[
                'ingredient'     => $ing,
                'packaging'      => $pkg,
                'ref_total_pack' => round($usage->total_pack, 1),
                'active_days'    => $usage->active_days,
                'avg_daily_pack' => round($avgDailyPack, 2),
                'stock_base'     => round($stockBase, 2),
                'stock_pack'     => round($stockPack, 1),
                'stock_dus'      => round($stockDus, 1),
                'days_cover'     => $daysToCover,
                'gross_pack'     => round($grossPack, 1),
                'buffer_pack'    => round($grossWithBuf - $grossPack, 1),
                'net_pack'       => round($netPack, 1),
                'net_dus'        => $netDus,
                'order1_dus'     => $order1Dus,
                'order2_dus'     => $order2Dus,
            ];
        }

        return compact(
            'store', 'tableData', 'orderDate', 'deliveryDate', 'coverageEnd', 'daysToCover',
            'leadTimeDays', 'refMonth', 'refYear', 'daysInRef', 'bufferPct',
            'splitOrder', 'splitDate', 'stockSource', 'selectedOpname'
        );
    }
}
