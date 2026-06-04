<?php
namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\{MonthlyRevenue, MonthlySale};
use Illuminate\Http\Request;

class HppTrendController extends Controller
{
    // Reuse the HPP calculation from HppController
    private HppController $hppCtrl;

    public function __construct()
    {
        $this->hppCtrl = new HppController();
    }

    public function index(Request $request)
    {
        $storeIds = auth()->user()->accessibleStoreIds();
        $stores   = auth()->user()->accessibleStores();

        // Default: last 6 months end_month periods
        $endYear   = (int)($request->year  ?? now()->year);
        $endMonth  = (int)($request->month ?? now()->month);
        $periods   = (int)($request->periods ?? 6);
        $periods   = max(2, min(12, $periods));
        $periodType = $request->period_type ?? 'end_month';

        // Validate selected stores
        $selectedIds = collect($request->store_ids ?? [$storeIds[0] ?? null])
            ->map(fn($id) => (int)$id)
            ->filter(fn($id) => in_array($id, $storeIds))
            ->values()->all();

        // Build list of (year, month) going back N periods
        $periodList = [];
        $cur = \Carbon\Carbon::create($endYear, $endMonth, 1);
        for ($i = 0; $i < $periods; $i++) {
            $periodList[] = ['year' => (int)$cur->year, 'month' => (int)$cur->month];
            $cur->subMonth();
        }
        $periodList = array_reverse($periodList);

        // Build trend data per store
        $trendData = [];
        foreach ($selectedIds as $sid) {
            $store  = $stores->firstWhere('id', $sid);
            $points = [];
            foreach ($periodList as $p) {
                // Use reflection to call private method — or we reuse via a public wrapper
                $result = $this->calcHpp($sid, $p['month'], $p['year'], $periodType);
                $points[] = (object)[
                    'label'          => $this->periodLabel($p['month'], $p['year']),
                    'month'          => $p['month'],
                    'year'           => $p['year'],
                    'omset'          => $result ? $result['summary']->omset : null,
                    'hpp_ideal'      => $result ? $result['summary']->hpp_ideal : null,
                    'hpp_aktual'     => $result ? $result['summary']->hpp_aktual : null,
                    'pct_hpp_ideal'  => $result ? $result['summary']->pct_hpp_ideal : null,
                    'pct_hpp_aktual' => $result ? $result['summary']->pct_hpp_aktual : null,
                    'margin_ideal'   => $result ? $result['summary']->margin_ideal : null,
                    'margin_aktual'  => $result ? $result['summary']->margin_aktual : null,
                ];
            }
            $trendData[] = (object)['store' => $store, 'points' => $points];
        }

        // Period labels for chart x-axis
        $labels = array_map(fn($p) => $this->periodLabel($p['month'], $p['year']), $periodList);

        return view('sales.hpp-trend', compact(
            'stores', 'selectedIds', 'endMonth', 'endYear',
            'periods', 'periodType', 'trendData', 'labels'
        ));
    }

    // Delegate to HppController via reflection (private method workaround)
    private function calcHpp(int $storeId, int $month, int $year, string $periodType): ?array
    {
        $ref = new \ReflectionMethod(HppController::class, 'calcHppForStore');
        $ref->setAccessible(true);
        return $ref->invoke($this->hppCtrl, $storeId, $month, $year, $periodType);
    }

    private function periodLabel(int $month, int $year): string
    {
        $months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
        return $months[$month - 1] . ' ' . $year;
    }
}
