<?php
namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\{WasteLog, WasteLogItem, IngredientCategory};
use Illuminate\Http\Request;
use Carbon\Carbon;

class WasteAnalysisController extends Controller
{
    public function index(Request $request)
    {
        $storeIds = auth()->user()->accessibleStoreIds();
        $stores   = auth()->user()->accessibleStores();

        $month   = (int)($request->month   ?? now()->month);
        $year    = (int)($request->year    ?? now()->year);
        $storeId = $request->store_id ? (int)$request->store_id : ($storeIds[0] ?? null);

        if (!$storeId || !in_array($storeId, $storeIds)) {
            return $this->emptyView($stores, $month, $year, $storeId);
        }

        $dateFrom = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
        $dateTo   = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        // Current month waste items
        $items = WasteLogItem::with(['ingredient.packagings', 'wasteLog'])
            ->whereHas('wasteLog', fn($q) =>
                $q->where('store_id', $storeId)
                  ->whereBetween('waste_date', [$dateFrom, $dateTo])
            )->get();

        // Per-ingredient aggregation
        $ingRows = $items->groupBy('ingredient_id')->map(function ($rows) {
            $ing = $rows->first()->ingredient;
            $totalBase  = $rows->sum('qty_base');
            $totalLoss  = $rows->sum('subtotal_loss');
            $avgPrice   = $totalBase > 0 ? $totalLoss / $totalBase : 0;
            $pkg        = $ing?->packagings->first();
            $dusSize    = $pkg ? ($pkg->crate_to_pack * $pkg->pack_to_base) : null;
            return (object)[
                'ingredient'  => $ing,
                'total_base'  => $totalBase,
                'total_loss'  => $totalLoss,
                'avg_price'   => $avgPrice,
                'total_dus'   => $dusSize ? $totalBase / $dusSize : null,
            ];
        })->sortByDesc('total_loss')->values();

        $totalLossMonth = $ingRows->sum('total_loss');

        // Per-day trend this month
        $dailyTrend = WasteLog::where('store_id', $storeId)
            ->whereBetween('waste_date', [$dateFrom, $dateTo])
            ->get(['waste_date', 'total_loss_amount'])
            ->sortBy('waste_date')
            ->map(fn($w) => (object)[
                'label' => Carbon::parse($w->waste_date)->format('d/m'),
                'total' => (float)$w->total_loss_amount,
            ])->values();

        // Monthly trend: last 6 months
        $monthlyTrend = collect();
        $cur = Carbon::create($year, $month, 1);
        for ($i = 0; $i < 6; $i++) {
            $mFrom = $cur->copy()->startOfMonth()->toDateString();
            $mTo   = $cur->copy()->endOfMonth()->toDateString();
            $total = WasteLog::where('store_id', $storeId)
                ->whereBetween('waste_date', [$mFrom, $mTo])
                ->sum('total_loss_amount');
            $monthlyTrend->prepend((object)[
                'label' => $this->mLabel($cur->month, $cur->year),
                'total' => (float)$total,
            ]);
            $cur->subMonth();
        }

        // Top waste log entries this month
        $recentLogs = WasteLog::with(['items.ingredient'])
            ->where('store_id', $storeId)
            ->whereBetween('waste_date', [$dateFrom, $dateTo])
            ->orderByDesc('total_loss_amount')
            ->take(10)->get();

        return view('reports.waste', compact(
            'stores', 'month', 'year', 'storeId',
            'ingRows', 'totalLossMonth', 'dailyTrend', 'monthlyTrend', 'recentLogs'
        ));
    }

    private function emptyView($stores, $month, $year, $storeId)
    {
        return view('reports.waste', [
            'stores' => $stores, 'month' => $month, 'year' => $year, 'storeId' => $storeId,
            'ingRows' => collect(), 'totalLossMonth' => 0,
            'dailyTrend' => collect(), 'monthlyTrend' => collect(), 'recentLogs' => collect(),
        ]);
    }

    private function mLabel(int $m, int $y): string
    {
        $months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
        return $months[$m - 1] . ' ' . $y;
    }
}
