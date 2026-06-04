<?php
namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\{MonthlySale, MonthlyRevenue, Recipe, IngredientComposition, IngredientPackaging, MutationItem, Opname};
use App\Exports\ArrayExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HppController extends Controller
{
    // ── Load komposisi: [parent_id => [{child_id, child, qty_needed}]] ────────
    private function loadCompositionMap(): array
    {
        $map = [];
        IngredientComposition::with('child')->get()->each(function ($c) use (&$map) {
            $map[$c->parent_id][] = (object)[
                'child_id'   => $c->child_id,
                'child'      => $c->child,
                'qty_needed' => $c->qty_needed_exact,
            ];
        });
        return $map;
    }

    // ── Harga pembelian terakhir dari PT Zhisheng per bahan ──────────────────
    // Hanya mengambil transaksi purchase_zhisheng, tanpa batas stok (pakai harga
    // terakhir meskipun stok kosong). Tidak ada weighted-avg — murni harga terbaru.
    private function buildBatchPrice(int $storeId, array $ingIds, string $upToDate): \Illuminate\Support\Collection
    {
        if (empty($ingIds)) return collect();

        // Ambil harga terakhir dari Zhisheng secara global (semua toko) —
        // harga Rp 0 diabaikan, urutkan dari tgl terima terbaru.
        $rows = MutationItem::whereHas('mutation', fn($q) =>
                $q->where('status', 'confirmed')
                  ->where('delivery_date', '<=', $upToDate)
                  // termasuk opening_stock (harga dari opname) & purchase_supplier (supplier lokal)
                  ->whereIn('type', ['purchase_zhisheng', 'purchase_supplier', 'opening_stock', 'sale_internal'])
            )
            ->whereIn('ingredient_id', $ingIds)
            ->where('price_per_base', '>', 0)
            ->join('mutations', 'mutations.id', '=', 'mutation_items.mutation_id')
            ->orderByDesc('mutations.delivery_date')
            ->orderByDesc('mutations.id')
            ->get(['mutation_items.ingredient_id', 'mutation_items.price_per_base']);

        // Ambil harga terbaru (pertama setelah diurutkan DESC) per ingredient
        return $rows->groupBy('ingredient_id')
            ->map(fn($g) => (float) $g->first()->price_per_base);
    }

    // ── Core kalkulasi HPP untuk satu toko ────────────────────────────────────
    // Mengembalikan ['summary' => ..., 'menuRows' => ..., 'ingredientRows' => ...]
    // atau null jika tidak ada data penjualan.
    private function calcHppForStore(int $storeId, int $month, int $year, string $periodType): ?array
    {
        $monthStart = Carbon::create($year, $month, 1);
        $dateTo     = $periodType === 'mid_month'
                        ? Carbon::create($year, $month, 15)
                        : Carbon::create($year, $month)->endOfMonth();
        $dateEnd    = $dateTo->toDateString();

        $omset = MonthlyRevenue::where('store_id', $storeId)
            ->where('month', $month)->where('year', $year)
            ->where('period_type', $periodType)
            ->value('total_revenue') ?? 0;

        $sales = MonthlySale::with('menu')
            ->where('store_id', $storeId)
            ->where('month', $month)->where('year', $year)
            ->where('period_type', $periodType)->get();

        if ($sales->isEmpty()) return null;

        $menuIds    = $sales->pluck('menu_id')->unique()->all();
        // Resep PER TOKO: prioritas resep khusus toko ini; kalau tidak ada → resep default (store_id NULL)
        $allRecipes = Recipe::with('ingredient')
            ->whereIn('menu_id', $menuIds)
            ->where(fn($q) => $q->where('store_id', $storeId)->orWhereNull('store_id'))
            ->where('effective_from', '<=', $dateEnd)
            ->orderByRaw('store_id IS NULL ASC') // store-specific (NOT NULL) menang
            ->orderByDesc('effective_from')
            ->get()
            ->groupBy('menu_id')
            ->map(fn($g) => $g->groupBy('ingredient_id')->map(fn($r) => $r->first()));

        $compositionMap = $this->loadCompositionMap();

        $rawIngIds = [];
        foreach ($menuIds as $menuId) {
            foreach ($allRecipes[$menuId] ?? collect() as $recipe) {
                $id = $recipe->ingredient_id;
                if (isset($compositionMap[$id])) {
                    foreach ($compositionMap[$id] as $c) $rawIngIds[] = $c->child_id;
                } else {
                    $rawIngIds[] = $id;
                }
            }
        }
        $rawIngIds  = array_unique($rawIngIds);
        $batchPrice = $this->buildBatchPrice($storeId, $rawIngIds, $dateEnd);

        // ── menuRows ──────────────────────────────────────────────────────────
        $menuRows = $sales->map(function ($sale) use ($allRecipes, $compositionMap, $batchPrice) {
            $hppPerPcs = 0;
            $ingRows   = [];
            foreach ($allRecipes[$sale->menu_id] ?? collect() as $recipe) {
                $id = $recipe->ingredient_id;
                if (isset($compositionMap[$id])) {
                    foreach ($compositionMap[$id] as $comp) {
                        $price     = (float)($batchPrice[$comp->child_id] ?? 0);
                        $qtyPerPcs = $recipe->qty_usage * $comp->qty_needed;
                        $usage     = $sale->total_sold * $qtyPerPcs;
                        $hppPerPcs += $qtyPerPcs * $price;
                        $ingRows[] = (object)[
                            'ingredient'   => $comp->child,
                            'via_composed' => $recipe->ingredient->name,
                            'qty_per_pcs'  => $qtyPerPcs,
                            'total_usage'  => $usage,
                            'price'        => $price,
                            'hpp'          => $usage * $price,
                        ];
                    }
                } else {
                    $price     = (float)($batchPrice[$id] ?? 0);
                    $usage     = $sale->total_sold * $recipe->qty_usage;
                    $hppPerPcs += $recipe->qty_usage * $price;
                    $ingRows[] = (object)[
                        'ingredient'   => $recipe->ingredient,
                        'via_composed' => null,
                        'qty_per_pcs'  => $recipe->qty_usage,
                        'total_usage'  => $usage,
                        'price'        => $price,
                        'hpp'          => $usage * $price,
                    ];
                }
            }
            return (object)[
                'menu'        => $sale->menu,
                'total_sold'  => $sale->total_sold,
                'hpp_per_pcs' => $hppPerPcs,
                'hpp_ideal'   => $sale->total_sold * $hppPerPcs,
                'ingredients' => $ingRows,
            ];
        })->sortByDesc('hpp_ideal')->values();

        // ── ingredientRows ────────────────────────────────────────────────────
        $idealAgg = [];
        foreach ($menuRows as $row) {
            foreach ($row->ingredients as $ing) {
                $id = $ing->ingredient->id;
                if (!isset($idealAgg[$id])) {
                    $idealAgg[$id] = ['ingredient' => $ing->ingredient, 'usage_base' => 0.0, 'hpp' => 0.0, 'price' => $ing->price];
                }
                $idealAgg[$id]['usage_base'] += $ing->total_usage;
                $idealAgg[$id]['hpp']        += $ing->hpp;
            }
        }

        $prevMonth   = $monthStart->copy()->subMonth();
        $soAkhir     = Opname::where('store_id', $storeId)
            ->where('period_month', $month)->where('period_year', $year)
            ->where('period_type', $periodType)->where('status', 'approved')
            ->with('items')->first();
        $soAwal      = Opname::where('store_id', $storeId)
            ->where('period_month', $prevMonth->month)->where('period_year', $prevMonth->year)
            ->where('period_type', 'end_month')->where('status', 'approved')
            ->with('items')->first();

        $closingMap  = $soAkhir ? $soAkhir->items->pluck('physical_qty', 'ingredient_id')->map(fn($v) => (float)$v)->all() : [];
        $openingMap  = $soAwal  ? $soAwal->items->pluck('physical_qty', 'ingredient_id')->map(fn($v) => (float)$v)->all()  : [];

        // Pembelian MASUK ke toko ini — diakui pada tanggal terima (delivery_date),
        // fallback ke transaction_date jika belum diisi.
        $purchaseMap = MutationItem::whereHas('mutation', fn($q) =>
                $q->where('destination_store_id', $storeId)
                  ->where('status', 'confirmed')
                  ->whereBetween(\DB::raw('COALESCE(delivery_date, transaction_date)'), [$monthStart, $dateTo])
                  ->whereIn('type', ['purchase_zhisheng', 'sale_internal'])
            )
            ->whereIn('ingredient_id', $rawIngIds)
            ->get(['ingredient_id', 'total_in_base'])
            ->groupBy('ingredient_id')
            ->map(fn($g) => $g->sum('total_in_base'));

        // Pembelian Internal KELUAR dari toko ini (dijual ke toko lain) — BUKAN pemakaian,
        // harus dikeluarkan dari hitungan pemakaian aktual toko pengirim.
        $salesOutMap = MutationItem::whereHas('mutation', fn($q) =>
                $q->where('source_store_id', $storeId)
                  ->where('status', 'confirmed')
                  ->whereBetween(\DB::raw('COALESCE(delivery_date, transaction_date)'), [$monthStart, $dateTo])
                  ->where('type', 'sale_internal')
            )
            ->whereIn('ingredient_id', $rawIngIds)
            ->get(['ingredient_id', 'total_in_base'])
            ->groupBy('ingredient_id')
            ->map(fn($g) => $g->sum('total_in_base'));

        $packagingMap = IngredientPackaging::where('is_active', true)
            ->whereIn('ingredient_id', array_keys($idealAgg) ?: [0])
            ->orderBy('id')->get()
            ->groupBy('ingredient_id')->map(fn($g) => $g->first());

        $ingredientRows = collect($idealAgg)->map(function ($agg, $ingId) use (
            $batchPrice, $closingMap, $openingMap, $purchaseMap, $salesOutMap, $packagingMap, $soAkhir
        ) {
            $pkg      = $packagingMap[$ingId] ?? null;
            $dusSize  = ($pkg && $pkg->crate_to_pack && $pkg->pack_to_base)
                        ? (int)$pkg->crate_to_pack * (int)$pkg->pack_to_base : null;
            $avgPrice = (float)($batchPrice[$ingId] ?? 0); // harga beli terakhir dari Zhisheng
            $idealBase = (float)$agg['usage_base'];
            $hppIdeal  = (float)$agg['hpp'];
            $hasActual  = $soAkhir && array_key_exists($ingId, $closingMap);
            $actualBase = null; $hppAktual = null;

            if ($hasActual) {
                $openingQty  = $openingMap[$ingId] ?? 0;
                $purchaseQty = (float)($purchaseMap[$ingId] ?? 0);
                $salesOutQty = (float)($salesOutMap[$ingId] ?? 0);
                $closingQty  = $closingMap[$ingId];
                // Pemakaian aktual = (stok awal + beli masuk) − dijual ke toko lain − stok akhir.
                // Waste TIDAK dikurangi di sini (sengaja dihitung sebagai biaya).
                $actualBase  = max(0, $openingQty + $purchaseQty - $salesOutQty - $closingQty);
                $hppAktual   = $actualBase * $avgPrice;
            }

            return (object)[
                'ingredient'   => $agg['ingredient'],
                'dus_size'     => $dusSize,
                'avg_price'    => $avgPrice,
                'ideal_base'   => $idealBase,
                'ideal_dus'    => $dusSize ? $idealBase / $dusSize : null,
                'hpp_ideal'    => $hppIdeal,
                'has_actual'   => $hasActual,
                'actual_base'  => $actualBase,
                'actual_dus'   => ($dusSize && $hasActual) ? $actualBase / $dusSize : null,
                'hpp_aktual'   => $hppAktual,
                'selisih_base' => $hasActual ? $actualBase - $idealBase : null,
                'selisih_dus'  => ($dusSize && $hasActual) ? ($actualBase - $idealBase) / $dusSize : null,
                'selisih_hpp'  => $hasActual ? $hppAktual - $hppIdeal : null,
            ];
        })->sortByDesc('hpp_ideal')->values();

        // ── Summary ───────────────────────────────────────────────────────────
        $totalHppIdeal  = $menuRows->sum('hpp_ideal');
        $totalHppAktual = $ingredientRows->where('has_actual', true)->sum('hpp_aktual');
        $hasAktualAny   = $ingredientRows->where('has_actual', true)->isNotEmpty();

        $summary = (object)[
            'omset'         => (float)$omset,
            'hpp_ideal'     => $totalHppIdeal,
            'hpp_aktual'    => $hasAktualAny ? $totalHppAktual : null,
            'pct_hpp_ideal' => $omset > 0 ? ($totalHppIdeal / $omset * 100) : null,
            'pct_hpp_aktual'=> ($hasAktualAny && $omset > 0) ? ($totalHppAktual / $omset * 100) : null,
            'margin_ideal'  => $omset > 0 ? (1 - $totalHppIdeal / $omset) * 100 : null,
            'margin_aktual' => ($hasAktualAny && $omset > 0) ? (1 - $totalHppAktual / $omset) * 100 : null,
            'selisih_hpp'   => $hasAktualAny ? $totalHppAktual - $totalHppIdeal : null,
            'has_opname'    => $soAkhir !== null,
        ];

        return compact('summary', 'menuRows', 'ingredientRows');
    }

    // ─────────────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $storeIds   = auth()->user()->accessibleStoreIds();
        $month      = (int)($request->month      ?? now()->month);
        $year       = (int)($request->year       ?? now()->year);
        $stores     = auth()->user()->accessibleStores();
        $storeId    = $request->store_id         ?? ($storeIds[0] ?? null);
        $periodType = $request->period_type      ?? 'end_month';

        $empty = fn() => view('sales.hpp', [
            'stores'     => $stores, 'month' => $month,
            'year'       => $year,   'storeId' => $storeId,
            'periodType' => $periodType,
            'menuRows' => collect(), 'ingredientRows' => collect(), 'summary' => null,
        ]);

        if (!$storeId || !in_array($storeId, $storeIds)) return $empty();

        $result = $this->calcHppForStore((int)$storeId, $month, $year, $periodType);
        if (!$result) return $empty();

        return view('sales.hpp', array_merge($result, [
            'stores' => $stores, 'month' => $month,
            'year'   => $year,   'storeId' => $storeId,
            'periodType' => $periodType,
        ]));
    }

    // ── Export HPP ───────────────────────────────────────────────────────────
    public function export(Request $request)
    {
        $storeIds   = auth()->user()->accessibleStoreIds();
        $storeId    = (int)($request->store_id ?? ($storeIds[0] ?? null));
        $month      = (int)($request->month ?? now()->month);
        $year       = (int)($request->year  ?? now()->year);
        $periodType = $request->period_type ?? 'end_month';

        if (!$storeId || !in_array($storeId, $storeIds)) abort(403);

        $result = $this->calcHppForStore($storeId, $month, $year, $periodType);
        if (!$result) abort(404, 'Tidak ada data HPP untuk periode ini.');

        $store     = \App\Models\Store::find($storeId);
        $label     = \Carbon\Carbon::create($year, $month)->isoFormat('MMMM Y');
        $period    = $periodType === 'mid_month' ? 'Tengah Bulan' : 'Akhir Bulan';
        $summary   = $result['summary'];
        $menuRows  = $result['menuRows'];
        $ingRows   = $result['ingredientRows'];

        $data = [];

        // Summary
        $data[] = ["LAPORAN HPP — {$store->name} — {$label} ({$period})"];
        $data[] = [];
        $data[] = ['Omset', $summary->omset];
        $data[] = ['HPP Ideal', $summary->hpp_ideal];
        $data[] = ['% HPP Ideal', $summary->pct_hpp_ideal ? number_format($summary->pct_hpp_ideal, 2) . '%' : '-'];
        $data[] = ['HPP Aktual', $summary->hpp_aktual ?? '-'];
        $data[] = ['% HPP Aktual', $summary->pct_hpp_aktual ? number_format($summary->pct_hpp_aktual, 2) . '%' : '-'];
        $data[] = ['Margin Ideal', $summary->margin_ideal ? number_format($summary->margin_ideal, 2) . '%' : '-'];
        $data[] = [];

        // Menu Rows
        $data[] = ['--- DETAIL PER MENU ---'];
        $data[] = ['Menu', 'Qty Terjual', 'HPP/Pcs', 'Total HPP Ideal'];
        foreach ($menuRows as $r) {
            $data[] = [$r->menu?->name ?? '-', $r->total_sold,
                number_format($r->hpp_per_pcs, 2), $r->hpp_ideal];
        }
        $data[] = ['TOTAL', $menuRows->sum('total_sold'), '', $menuRows->sum('hpp_ideal')];
        $data[] = [];

        // Ingredient Rows
        $data[] = ['--- DETAIL PER BAHAN ---'];
        $data[] = ['Bahan', 'Satuan', 'Harga Avg/Base', 'Pemakaian Ideal (base)', 'HPP Ideal', 'Pemakaian Aktual (base)', 'HPP Aktual', 'Selisih HPP'];
        foreach ($ingRows as $r) {
            $data[] = [
                $r->ingredient->name,
                $r->ingredient->unit_base,
                $r->avg_price,
                number_format($r->ideal_base, 3),
                $r->hpp_ideal,
                $r->actual_base !== null ? number_format($r->actual_base, 3) : '-',
                $r->hpp_aktual ?? '-',
                $r->selisih_hpp ?? '-',
            ];
        }

        return Excel::download(
            new ArrayExport($data),
            "hpp_{$store->name}_{$month}-{$year}.xlsx"
        );
    }

    // ── Perbandingan HPP multi-toko ───────────────────────────────────────────
    public function compare(Request $request)
    {
        $storeIds   = auth()->user()->accessibleStoreIds();
        $stores     = auth()->user()->accessibleStores();
        $month      = (int)($request->month      ?? now()->month);
        $year       = (int)($request->year       ?? now()->year);
        $periodType = $request->period_type      ?? 'end_month';

        // Validasi: hanya store yang boleh diakses user
        $selectedIds = collect($request->store_ids ?? [])
            ->map(fn($id) => (int)$id)
            ->filter(fn($id) => in_array($id, $storeIds))
            ->values()->all();

        $compareData = [];
        foreach ($selectedIds as $sid) {
            $result  = $this->calcHppForStore($sid, $month, $year, $periodType);
            $compareData[] = (object)[
                'store'   => $stores->firstWhere('id', $sid),
                'summary' => $result ? $result['summary'] : null,
                'menuRows'=> $result ? $result['menuRows'] : collect(),
            ];
        }

        return view('sales.hpp-compare', compact(
            'stores', 'selectedIds', 'month', 'year', 'periodType', 'compareData'
        ));
    }
}
