<?php
namespace App\Http\Controllers;

use App\Models\{DailyUsage, Ingredient, IngredientCategory, IngredientPackaging, Opname, OpnameItem, Store, StoreStock};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $stockSource  = $request->input('stock_source', 'fifo');
        $opnameId     = $request->filled('opname_id') ? (int)$request->opname_id : null;
        $daysToCover  = $deliveryDate->diffInDays($coverageEnd);

        // Lead time (informasi saja, tidak mempengaruhi kalkulasi)
        $leadTimeDays = $orderDate ? $orderDate->diffInDays($deliveryDate) : null;

        // ── Sumber stok saat ini ──────────────────────────────────────────────
        $selectedOpname = null;
        if ($stockSource === 'opname' && $opnameId) {
            $selectedOpname = Opname::find($opnameId);
            // physical_qty = base unit dari opname fisik.
            // Jumlahkan per ingredient (1 bahan bisa punya >1 baris kemasan).
            $stockMap = OpnameItem::where('opname_id', $opnameId)
                ->get(['ingredient_id', 'physical_qty'])
                ->groupBy('ingredient_id')
                ->map(fn($g) => (float)$g->sum('physical_qty'));
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
            ->whereExists(fn($q) => $q
                ->from('daily_confirmations')
                ->whereColumn('daily_confirmations.store_id', 'daily_usages.store_id')
                ->whereColumn('daily_confirmations.confirmation_date', 'daily_usages.usage_date')
            )
            ->groupBy('ingredient_id')
            ->selectRaw('ingredient_id, SUM(qty_pack) as total_pack, COUNT(DISTINCT usage_date) as active_days')
            ->get()
            ->keyBy('ingredient_id');

        // ── Fallback HPP Aktual ───────────────────────────────────────────────
        // Jika tidak ada pencatatan harian yang dikonfirmasi, hitung konsumsi dari:
        // konsumsi_base = stok_awal (opname bulan lalu) + pembelian_bulan_ini - stok_akhir (opname bulan ini)
        $usageSource = 'daily'; // untuk info di view
        if ($usageSums->isEmpty()) {
            $prevPeriod   = Carbon::create($refYear, $refMonth, 1)->subMonth();
            $openingOpname = Opname::where('store_id', $storeId)
                ->where('period_month', $prevPeriod->month)
                ->where('period_year',  $prevPeriod->year)
                ->where('period_type',  'end_month')
                ->where('status',       'approved')
                ->first();
            $closingOpname = Opname::where('store_id', $storeId)
                ->where('period_month', $refMonth)
                ->where('period_year',  $refYear)
                ->where('period_type',  'end_month')
                ->where('status',       'approved')
                ->first();

            if ($openingOpname || $closingOpname) {
                // Stok awal per ingredient (base) dari opname bulan sebelumnya.
                // Jumlahkan per ingredient (1 bahan bisa punya >1 baris kemasan).
                $openingMap = $openingOpname
                    ? OpnameItem::where('opname_id', $openingOpname->id)
                        ->get(['ingredient_id', 'physical_qty'])
                        ->groupBy('ingredient_id')
                        ->map(fn($g) => (float)$g->sum('physical_qty'))
                    : collect();

                // Stok akhir per ingredient (base) dari opname bulan ini
                $closingMap = $closingOpname
                    ? OpnameItem::where('opname_id', $closingOpname->id)
                        ->get(['ingredient_id', 'physical_qty'])
                        ->groupBy('ingredient_id')
                        ->map(fn($g) => (float)$g->sum('physical_qty'))
                    : collect();

                // Pembelian / barang masuk bulan ini (base) per ingredient
                $purchaseMap = DB::table('mutation_items as mi')
                    ->join('mutations as m', 'm.id', '=', 'mi.mutation_id')
                    ->where('m.destination_store_id', $storeId)
                    ->where('m.status', 'confirmed')
                    ->whereBetween(DB::raw('COALESCE(m.delivery_date, m.transaction_date)'), [$refStart, $refEnd])
                    ->whereIn('m.type', ['purchase_zhisheng', 'purchase_supplier', 'sale_internal', 'sale_external'])
                    ->selectRaw('mi.ingredient_id, SUM(mi.total_in_base) as total')
                    ->groupBy('mi.ingredient_id')
                    ->pluck('total', 'ingredient_id')
                    ->map(fn($v) => (float)$v);

                // Transfer keluar (base) per ingredient — toko ini sebagai sumber.
                // Sama dengan HPP Aktual: konsumsi mengurangi transfer keluar.
                $salesOutMap = DB::table('mutation_items as mi')
                    ->join('mutations as m', 'm.id', '=', 'mi.mutation_id')
                    ->where('m.source_store_id', $storeId)
                    ->where('m.status', 'confirmed')
                    ->whereBetween(DB::raw('COALESCE(m.delivery_date, m.transaction_date)'), [$refStart, $refEnd])
                    ->where('m.type', 'sale_internal')
                    ->selectRaw('mi.ingredient_id, SUM(mi.total_in_base) as total')
                    ->groupBy('mi.ingredient_id')
                    ->pluck('total', 'ingredient_id')
                    ->map(fn($v) => (float)$v);

                // Hitung konsumsi per ingredient.
                // Gunakan kemasan pertama (id terkecil) agar konsisten dgn tabel.
                $allIngIds = $openingMap->keys()->merge($closingMap->keys())->unique();
                $pkgMap = IngredientPackaging::whereIn('ingredient_id', $allIngIds)
                    ->where('is_active', true)
                    ->orderBy('id')
                    ->get()->groupBy('ingredient_id')->map(fn($g) => $g->first());

                foreach ($allIngIds as $ingId) {
                    $pkg = $pkgMap[$ingId] ?? null;
                    if (!$pkg || $pkg->pack_to_base <= 0) continue;

                    $opening    = $openingMap[$ingId]  ?? 0.0;
                    $closing    = $closingMap[$ingId]  ?? 0.0;
                    $purchases  = $purchaseMap[$ingId] ?? 0.0;
                    $salesOut   = $salesOutMap[$ingId] ?? 0.0;
                    // Identik dengan HPP Aktual: opening + masuk − transfer keluar − closing
                    $consumBase = $opening + $purchases - $salesOut - $closing;
                    if ($consumBase <= 0) continue;

                    $consumPack = $consumBase / $pkg->pack_to_base;
                    $usageSums->put($ingId, (object)[
                        'ingredient_id' => $ingId,
                        'total_pack'    => $consumPack,
                        'active_days'   => $daysInRef, // asumsi full bulan
                    ]);
                }
                $usageSource = 'hpp';
            }

            if ($usageSums->isEmpty()) {
                return compact('store', 'orderDate', 'deliveryDate', 'coverageEnd', 'daysToCover',
                    'leadTimeDays', 'refMonth', 'refYear', 'daysInRef', 'bufferPct',
                    'stockSource', 'selectedOpname')
                    + ['tableData' => [], 'message' => 'Tidak ada data konsumsi (pencatatan harian maupun opname) untuk bulan referensi yang dipilih.'];
            }
        }

        // Urutan sama dengan SO / pencatatan harian: kategori sort_order → ingredient id
        $catSort     = IngredientCategory::pluck('sort_order', 'name')->toArray();
        $ingredients = Ingredient::with(['packagings' => fn($q) => $q->where('is_active', true)->orderBy('id')])
            ->whereIn('id', $usageSums->keys())
            ->where('type', '!=', 'semi_finished')
            ->get()
            ->sort(function ($a, $b) use ($catSort) {
                $ai = $catSort[$a->category] ?? 9999;
                $bi = $catSort[$b->category] ?? 9999;
                return $ai !== $bi ? $ai <=> $bi : $a->id <=> $b->id;
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
            $netDusRaw    = $netPack / $pkg->crate_to_pack;
            // Kebutuhan < 0,1 dus dianggap nol (jangan dibulatkan ke atas)
            $netDus       = $netDusRaw < 0.1 ? 0 : (int) ceil($netDusRaw);

            $ctp = $pkg->crate_to_pack;
            $tableData[] = (object)[
                'ingredient'      => $ing,
                'packaging'       => $pkg,
                'ref_total_dus'   => round($usage->total_pack / $ctp, 2),
                'avg_daily_dus'   => round($avgDailyPack / $ctp, 3),
                'active_days'     => $usage->active_days,
                'stock_dus'       => round($stockDus, 2),
                'gross_dus'       => round($grossPack / $ctp, 2),
                'buffer_dus'      => round(($grossWithBuf - $grossPack) / $ctp, 2),
                'net_dus'         => $netDus,
            ];
        }

        return compact(
            'store', 'tableData', 'orderDate', 'deliveryDate', 'coverageEnd', 'daysToCover',
            'leadTimeDays', 'refMonth', 'refYear', 'daysInRef', 'bufferPct',
            'stockSource', 'selectedOpname', 'usageSource'
        );
    }
}
