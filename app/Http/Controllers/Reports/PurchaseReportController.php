<?php
namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\{Mutation, MutationItem, Supplier, Store};
use Illuminate\Http\Request;
use Carbon\Carbon;

class PurchaseReportController extends Controller
{
    public function index(Request $request)
    {
        $storeIds = auth()->user()->accessibleStoreIds();
        $stores   = auth()->user()->accessibleStores();

        $month      = (int)($request->month ?? now()->month);
        $year       = (int)($request->year  ?? now()->year);
        $storeId    = $request->store_id ? (int)$request->store_id : ($storeIds[0] ?? null);
        $supplierId = $request->supplier_id ? (int)$request->supplier_id : null;

        // Validate store access
        if (!$storeId || !in_array($storeId, $storeIds)) {
            return view('reports.purchase', [
                'stores' => $stores, 'month' => $month, 'year' => $year,
                'storeId' => $storeId, 'supplierId' => $supplierId,
                'suppliers' => Supplier::orderBy('name')->get(),
                'supplierRows' => collect(), 'grandTotal' => 0,
                'monthlyTrend' => collect(),
            ]);
        }

        $dateFrom = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
        $dateTo   = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        // All purchase mutations for this store in this month
        $purchaseTypes = ['purchase_zhisheng', 'purchase_supplier', 'opening_stock'];

        $query = Mutation::with(['supplier', 'items.ingredient', 'items.packaging'])
            ->where('destination_store_id', $storeId)
            ->where('status', 'confirmed')
            ->whereIn('type', $purchaseTypes)
            ->whereBetween('transaction_date', [$dateFrom, $dateTo]);

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        $mutations = $query->orderBy('transaction_date')->get();

        // Group by supplier
        $supplierRows = $mutations->groupBy('supplier_id')->map(function ($muts, $supId) {
            $supplier   = $muts->first()->supplier;
            $totalValue = $muts->flatMap->items->sum('cost_subtotal');
            $invoices   = $muts->map(function ($m) {
                return (object)[
                    'id'               => $m->id,
                    'transaction_date' => $m->transaction_date,
                    'delivery_date'    => $m->delivery_date,
                    'reference_no'     => $m->reference_no,
                    'invoice_no'       => $m->invoice_no,
                    'type_label'       => $m->type_label,
                    'total'            => $m->items->sum('cost_subtotal'),
                    'items'            => $m->items,
                ];
            });

            // Per-ingredient summary for this supplier this month
            $ingAgg = $muts->flatMap->items->groupBy('ingredient_id')->map(function ($rows) {
                $ing = $rows->first()->ingredient;
                return (object)[
                    'ingredient'  => $ing,
                    'total_base'  => $rows->sum('total_in_base'),
                    'total_value' => $rows->sum('cost_subtotal'),
                    'avg_price'   => $rows->sum('total_in_base') > 0
                        ? $rows->sum('cost_subtotal') / $rows->sum('total_in_base')
                        : 0,
                ];
            })->sortByDesc('total_value')->values();

            return (object)[
                'supplier'   => $supplier,
                'total'      => $totalValue,
                'invoices'   => $invoices,
                'ing_rows'   => $ingAgg,
            ];
        })->sortByDesc('total')->values();

        $grandTotal = $supplierRows->sum('total');

        // Monthly trend: last 6 months for chart
        $trendMonths = 6;
        $monthlyTrend = collect();
        $cur = Carbon::create($year, $month, 1);
        for ($i = 0; $i < $trendMonths; $i++) {
            $mFrom = $cur->copy()->startOfMonth()->toDateString();
            $mTo   = $cur->copy()->endOfMonth()->toDateString();
            $total = MutationItem::whereHas('mutation', fn($q) =>
                    $q->where('destination_store_id', $storeId)
                      ->where('status', 'confirmed')
                      ->whereIn('type', $purchaseTypes)
                      ->whereBetween('transaction_date', [$mFrom, $mTo])
                )->sum('cost_subtotal');

            $monthlyTrend->prepend((object)[
                'label' => $this->mLabel($cur->month, $cur->year),
                'total' => (float)$total,
            ]);
            $cur->subMonth();
        }

        $suppliers = Supplier::orderBy('name')->get();

        return view('reports.purchase', compact(
            'stores', 'month', 'year', 'storeId', 'supplierId',
            'suppliers', 'supplierRows', 'grandTotal', 'monthlyTrend'
        ));
    }

    private function mLabel(int $m, int $y): string
    {
        $months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
        return $months[$m - 1] . ' ' . $y;
    }
}
