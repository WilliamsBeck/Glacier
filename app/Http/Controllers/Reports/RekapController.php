<?php
namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\{WasteLog, MutationItem, MonthlyRevenue, MonthlySale, Mutation, Store};
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\{WasteExport, PurchaseExport, SalesExport};

class RekapController extends Controller
{
    public function index(Request $request)
    {
        $storeIds = auth()->user()->accessibleStoreIds();
        $stores   = auth()->user()->accessibleStores();

        $month   = (int)($request->month ?? now()->month);
        $year    = (int)($request->year  ?? now()->year);

        $dateFrom = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
        $dateTo   = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        // ── Ringkasan per toko ──────────────────────────────────────────────────
        $summaries = $stores->map(function ($store) use ($storeIds, $month, $year, $dateFrom, $dateTo) {
            if (!in_array($store->id, $storeIds)) return null;

            $totalPurchase = MutationItem::whereHas('mutation', fn($q) =>
                    $q->where('destination_store_id', $store->id)
                      ->where('status', 'confirmed')
                      ->whereIn('type', ['purchase_zhisheng', 'purchase_supplier', 'opening_stock'])
                      ->whereBetween('transaction_date', [$dateFrom, $dateTo])
                )->sum('cost_subtotal');

            $totalWaste = WasteLog::where('store_id', $store->id)
                ->whereBetween('waste_date', [$dateFrom, $dateTo])
                ->sum('total_loss_amount');

            $omset = MonthlyRevenue::where('store_id', $store->id)
                ->where('month', $month)->where('year', $year)
                ->where('period_type', 'end_month')
                ->value('total_revenue') ?? 0;

            return (object)[
                'store'          => $store,
                'omset'          => (float)$omset,
                'total_purchase' => (float)$totalPurchase,
                'total_waste'    => (float)$totalWaste,
                'hpp_ratio'      => $omset > 0 ? ($totalPurchase / $omset * 100) : null,
                'waste_ratio'    => $omset > 0 ? ($totalWaste / $omset * 100) : null,
            ];
        })->filter()->values();

        return view('reports.rekap', compact('stores', 'month', 'year', 'summaries'));
    }

    // ── Export Waste ──────────────────────────────────────────────────────────
    public function exportWaste(Request $request)
    {
        $storeIds = auth()->user()->accessibleStoreIds();
        $storeId  = $request->store_id ? (int)$request->store_id : ($storeIds[0] ?? null);
        $month    = (int)($request->month ?? now()->month);
        $year     = (int)($request->year  ?? now()->year);

        if (!$storeId || !in_array($storeId, $storeIds)) abort(403);

        $store    = Store::find($storeId);
        $dateFrom = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
        $dateTo   = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        $rows = \App\Models\WasteLogItem::with(['ingredient', 'wasteLog'])
            ->whereHas('wasteLog', fn($q) =>
                $q->where('store_id', $storeId)
                  ->whereBetween('waste_date', [$dateFrom, $dateTo])
            )->get();

        $data = [['Toko', 'Tanggal', 'Bahan', 'Qty Base', 'Satuan', 'Harga/Base', 'Kerugian', 'Catatan']];
        foreach ($rows as $r) {
            $data[] = [
                $store->name,
                Carbon::parse($r->wasteLog->waste_date)->format('d/m/Y'),
                $r->ingredient->name ?? '-',
                $r->qty_base,
                $r->ingredient->unit_base ?? '-',
                $r->price_per_base,
                $r->subtotal_loss,
                $r->wasteLog->notes ?? '',
            ];
        }

        $months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
        $filename = 'waste_' . $store->name . '_' . $months[$month - 1] . $year . '.xlsx';

        return Excel::download(new \App\Exports\ArrayExport($data), $filename);
    }

    // ── Export Pembelian ───────────────────────────────────────────────────────
    public function exportPurchase(Request $request)
    {
        $storeIds = auth()->user()->accessibleStoreIds();
        $storeId  = $request->store_id ? (int)$request->store_id : ($storeIds[0] ?? null);
        $month    = (int)($request->month ?? now()->month);
        $year     = (int)($request->year  ?? now()->year);

        if (!$storeId || !in_array($storeId, $storeIds)) abort(403);

        $store    = Store::find($storeId);
        $dateFrom = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
        $dateTo   = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        $mutations = Mutation::with(['supplier', 'items.ingredient'])
            ->where('destination_store_id', $storeId)
            ->where('status', 'confirmed')
            ->whereIn('type', ['purchase_zhisheng', 'purchase_supplier', 'opening_stock'])
            ->whereBetween('transaction_date', [$dateFrom, $dateTo])
            ->orderBy('transaction_date')
            ->get();

        $data = [['Toko', 'Tanggal', 'Tipe', 'Supplier', 'Ref / Invoice', 'Bahan', 'Qty Base', 'Harga/Base', 'Subtotal']];
        foreach ($mutations as $m) {
            foreach ($m->items as $item) {
                $data[] = [
                    $store->name,
                    $m->transaction_date->format('d/m/Y'),
                    $m->type_label,
                    $m->supplier?->name ?? '-',
                    ($m->reference_no ?? '') . ($m->invoice_no ? ' / ' . $m->invoice_no : ''),
                    $item->ingredient->name ?? '-',
                    $item->total_in_base,
                    $item->price_per_base,
                    $item->cost_subtotal,
                ];
            }
        }

        $months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
        $filename = 'pembelian_' . $store->name . '_' . $months[$month - 1] . $year . '.xlsx';

        return Excel::download(new \App\Exports\ArrayExport($data), $filename);
    }

    // ── Export HPP ────────────────────────────────────────────────────────────
    public function exportHpp(Request $request)
    {
        $storeIds   = auth()->user()->accessibleStoreIds();
        $storeId    = $request->store_id ? (int)$request->store_id : ($storeIds[0] ?? null);
        $month      = (int)($request->month ?? now()->month);
        $year       = (int)($request->year  ?? now()->year);
        $periodType = $request->period_type ?? 'end_month';

        if (!$storeId || !in_array($storeId, $storeIds)) abort(403);

        $store  = Store::find($storeId);
        $ctrl   = new \App\Http\Controllers\Sales\HppController();
        $ref    = new \ReflectionMethod($ctrl, 'calcHppForStore');
        $ref->setAccessible(true);
        $result = $ref->invoke($ctrl, $storeId, $month, $year, $periodType);

        if (!$result) abort(404, 'Tidak ada data HPP');

        $data = [['Menu', 'Total Terjual', 'HPP/Pcs', 'HPP Ideal']];
        foreach ($result['menuRows'] as $row) {
            $data[] = [
                $row->menu->name ?? '-',
                $row->total_sold,
                $row->hpp_per_pcs,
                $row->hpp_ideal,
            ];
        }
        $data[] = [];
        $data[] = ['', '', 'TOTAL HPP Ideal', $result['summary']->hpp_ideal];
        $data[] = ['', '', 'Omset', $result['summary']->omset];
        $data[] = ['', '', '% HPP', ($result['summary']->pct_hpp_ideal ? number_format($result['summary']->pct_hpp_ideal, 2) . '%' : '-')];

        $months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
        $filename = 'hpp_' . $store->name . '_' . $months[$month - 1] . $year . '.xlsx';

        return Excel::download(new \App\Exports\ArrayExport($data), $filename);
    }
}
