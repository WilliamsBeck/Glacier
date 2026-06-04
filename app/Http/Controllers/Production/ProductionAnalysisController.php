<?php
namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Models\{ProductionLog, ProductionLogItem};
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProductionAnalysisController extends Controller
{
    public function index(Request $request)
    {
        $storeIds = auth()->user()->accessibleStoreIds();
        $stores   = auth()->user()->accessibleStores();

        $month   = (int)($request->month ?? now()->month);
        $year    = (int)($request->year  ?? now()->year);
        $storeId = $request->store_id ? (int)$request->store_id : ($storeIds[0] ?? null);

        if (!$storeId || !in_array($storeId, $storeIds)) {
            return $this->emptyView($stores, $month, $year, $storeId);
        }

        $dateFrom = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
        $dateTo   = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();
        $days     = Carbon::create($year, $month, 1)->daysInMonth;

        // Semua log produksi bulan ini
        $logs = ProductionLog::with(['semiFinished', 'items.rawIngredient'])
            ->where('store_id', $storeId)
            ->whereBetween('production_date', [$dateFrom, $dateTo])
            ->orderBy('production_date')
            ->get();

        // Total cost dari bahan baku yang dikonsumsi
        $totalCost = $logs->flatMap->items
            ->sum(fn($item) => $item->qty_consumed * $item->price_per_base);

        // Per bahan setengah jadi (semi_finished)
        $productRows = $logs->groupBy('semi_finished_id')->map(function ($group) use ($days) {
            $ing      = $group->first()->semiFinished;
            $batches  = $group->count();
            $totalQty = $group->sum('qty_produced');
            $cost     = $group->flatMap->items
                ->sum(fn($item) => $item->qty_consumed * $item->price_per_base);

            // Per-bahan baku yang dikonsumsi
            $ingRows = $group->flatMap->items
                ->groupBy('raw_ingredient_id')
                ->map(function ($rows) {
                    $ing = $rows->first()->rawIngredient;
                    $qty = $rows->sum('qty_consumed');
                    $val = $rows->sum(fn($r) => $r->qty_consumed * $r->price_per_base);
                    return (object)[
                        'ingredient'  => $ing,
                        'qty_consumed'=> $qty,
                        'total_cost'  => $val,
                        'avg_price'   => $qty > 0 ? $val / $qty : 0,
                    ];
                })->sortByDesc('total_cost')->values();

            return (object)[
                'ingredient'  => $ing,
                'batches'     => $batches,
                'total_qty'   => $totalQty,
                'avg_per_day' => $totalQty / $days,
                'cost'        => $cost,
                'cost_per_batch' => $batches > 0 ? $cost / $batches : 0,
                'ing_rows'    => $ingRows,
            ];
        })->sortByDesc('cost')->values();

        // Trend harian bulan ini
        $dailyTrend = $logs->groupBy(fn($l) => $l->production_date->format('d/m'))
            ->map(fn($g) => (object)[
                'label'   => $g->first()->production_date->format('d/m'),
                'batches' => $g->count(),
                'cost'    => $g->flatMap->items->sum(fn($i) => $i->qty_consumed * $i->price_per_base),
            ])->values();

        // Trend 6 bulan terakhir
        $monthlyTrend = collect();
        $cur = Carbon::create($year, $month, 1);
        for ($i = 0; $i < 6; $i++) {
            $mFrom = $cur->copy()->startOfMonth()->toDateString();
            $mTo   = $cur->copy()->endOfMonth()->toDateString();

            $mLogs = ProductionLog::with('items')
                ->where('store_id', $storeId)
                ->whereBetween('production_date', [$mFrom, $mTo])
                ->get();

            $mCost = $mLogs->flatMap->items
                ->sum(fn($item) => $item->qty_consumed * $item->price_per_base);

            $monthlyTrend->prepend((object)[
                'label'   => $this->mLabel($cur->month, $cur->year),
                'batches' => $mLogs->count(),
                'cost'    => (float)$mCost,
            ]);
            $cur->subMonth();
        }

        return view('production.analysis', compact(
            'stores', 'month', 'year', 'storeId',
            'productRows', 'totalCost', 'days',
            'dailyTrend', 'monthlyTrend',
            'logs'
        ));
    }

    private function emptyView($stores, $month, $year, $storeId)
    {
        return view('production.analysis', [
            'stores' => $stores, 'month' => $month, 'year' => $year, 'storeId' => $storeId,
            'productRows' => collect(), 'totalCost' => 0, 'days' => 30,
            'dailyTrend' => collect(), 'monthlyTrend' => collect(), 'logs' => collect(),
        ]);
    }

    private function mLabel(int $m, int $y): string
    {
        $months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
        return $months[$m - 1] . ' ' . $y;
    }
}
