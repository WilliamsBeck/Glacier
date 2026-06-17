<?php
namespace App\Http\Controllers\Opname;

use App\Http\Controllers\Controller;
use App\Models\{Opname, OpnameItem, Ingredient, IngredientPackaging, MutationItem, Mutation, StockLedger, UnlockRequest};
use App\Services\{StockLedgerService, FifoService, MonthLockService};
use App\Exports\ArrayExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Style\{Fill, Font, Alignment, Border};

class OpnameController extends Controller
{
    public function index(Request $request)
    {
        $storeIds = auth()->user()->accessibleStoreIds();
        $query    = Opname::with(['store', 'performedBy'])
            ->whereIn('store_id', $storeIds);

        if ($request->store_id)    $query->where('store_id', $request->store_id);
        if ($request->period_type) $query->where('period_type', $request->period_type);
        if ($request->month)       $query->where('period_month', $request->month);
        if ($request->year)        $query->where('period_year', $request->year ?? now()->year);

        $opnames = $query->latest('opname_date')->paginate(20);
        $stores  = auth()->user()->accessibleStores();
        return view('opname.index', compact('opnames', 'stores'));
    }

    public function create()
    {
        $stores      = auth()->user()->accessibleStores();
        $ingredients = Ingredient::where('is_active', true)->where('type', '!=', 'semi_finished')
            ->leftJoin('ingredient_categories as ic', 'ingredients.category', '=', 'ic.name')
            ->orderByRaw('ic.sort_order IS NULL')->orderBy('ic.sort_order')->orderBy('ingredients.id')
            ->select('ingredients.*')->get();
        return view('opname.create', compact('stores', 'ingredients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'store_id'    => 'required|exists:stores,id',
            'opname_date' => 'required|date',
            'period_type' => 'required|in:mid_month,end_month',
        ]);

        $date       = $request->opname_date;
        $carbonDate = \Carbon\Carbon::parse($date);
        $periodType = $request->period_type;
        $month      = $carbonDate->month;
        $year       = $carbonDate->year;

        // ── Lock check: bulan sudah lewat H+7? ─────────────────────────────────
        if (!auth()->user()->isSuperAdmin() && MonthLockService::isPastLock($month, $year)) {
            return back()->withInput()
                ->with('error', MonthLockService::lockMessage($month, $year));
        }

        // Cek duplikat opname
        $exists = Opname::where('store_id', $request->store_id)
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->where('period_type', $periodType)
            ->exists();

        if ($exists) {
            return back()->withErrors(['error' => 'Opname periode ini sudah ada untuk toko ini.']);
        }

        $opname = null;
        DB::transaction(function () use ($request, $date, $month, $year, $periodType, &$opname) {
            $opname = Opname::create([
                'store_id'     => $request->store_id,
                'opname_date'  => $date,
                'period_month' => $month,
                'period_year'  => $year,
                'period_type'  => $periodType,
                'status'       => 'draft',
                'performed_by' => auth()->id(),
                'notes'        => $request->notes,
            ]);

            // ── Load per-packaging rows (sama seperti API systemQty) ────────────
            $allPackagings = IngredientPackaging::where('is_active', true)
                ->whereHas('ingredient', fn($q) => $q->where('type', '!=', 'semi_finished'))
                ->orderBy('ingredient_id')->orderBy('id')->get();
            $allIngredients = Ingredient::where('is_active', true)->where('type', '!=', 'semi_finished')
                ->leftJoin('ingredient_categories as ic', 'ingredients.category', '=', 'ic.name')
                ->orderByRaw('ic.sort_order IS NULL')->orderBy('ic.sort_order')->orderBy('ingredients.id')
                ->select('ingredients.*')->get();
            $ingIdsWithPkg  = $allPackagings->pluck('ingredient_id')->unique()->all();
            $pkgIds         = $allPackagings->pluck('id')->all();

            $remainingByPkg = MutationItem::whereHas('mutation', fn($q) =>
                    $q->where('destination_store_id', $request->store_id)->where('status', 'confirmed')
                      ->whereDate('transaction_date', '<=', $date)
                )
                ->whereIn('packaging_id', $pkgIds)
                ->selectRaw('packaging_id, SUM(remaining_qty) as total_remaining')
                ->groupBy('packaging_id')
                ->pluck('total_remaining', 'packaging_id');

            $ingNoPkg = $allIngredients->filter(fn($i) => !in_array($i->id, $ingIdsWithPkg));
            $remainingByIng = collect();
            if ($ingNoPkg->isNotEmpty()) {
                $remainingByIng = MutationItem::whereHas('mutation', fn($q) =>
                        $q->where('destination_store_id', $request->store_id)->where('status', 'confirmed')
                          ->whereDate('transaction_date', '<=', $date)
                    )
                    ->whereIn('ingredient_id', $ingNoPkg->pluck('id')->all())
                    ->whereNull('packaging_id')
                    ->selectRaw('ingredient_id, SUM(remaining_qty) as total_remaining')
                    ->groupBy('ingredient_id')
                    ->pluck('total_remaining', 'ingredient_id');
            }

            $submittedItems = $request->input('items', []);  // keyed by row_key

            $saveItem = function ($ingredientId, $packagingObj, $sysQty, $rowKey) use ($request, $opname, $submittedItems) {
                $data = $submittedItems[$rowKey] ?? [];

                $physicalCrate = isset($data['physical_crate']) && $data['physical_crate'] !== '' ? (int)$data['physical_crate'] : null;
                $physicalPack  = isset($data['physical_pack'])  && $data['physical_pack']  !== '' ? (int)$data['physical_pack']  : null;
                $physicalBase  = isset($data['physical_base'])  && $data['physical_base']  !== '' ? (float)$data['physical_base'] : null;

                if ($packagingObj && ($physicalCrate !== null || $physicalPack !== null || $physicalBase !== null)) {
                    $physicalQty = $packagingObj->convertToBase(
                        $physicalCrate ?? 0,
                        $physicalPack  ?? 0,
                        $physicalBase  ?? 0
                    );
                } elseif ($physicalBase !== null) {
                    $physicalQty = $physicalBase;
                } else {
                    $physicalQty = 0;
                }

                // Harga manual (per dus) → per base. Hanya tersimpan kalau user mengisi
                // (yaitu saat bahan belum punya harga dari data sebelumnya).
                $pricePerBase = null;
                if (isset($data['price_per_dus']) && $data['price_per_dus'] !== '' && (float)$data['price_per_dus'] > 0) {
                    $crateToBase = $packagingObj
                        ? (float)$packagingObj->crate_to_pack * (float)$packagingObj->pack_to_base : 0;
                    $pricePerBase = $crateToBase > 0
                        ? (float)$data['price_per_dus'] / $crateToBase
                        : (float)$data['price_per_dus'];
                }

                OpnameItem::create([
                    'opname_id'      => $opname->id,
                    'ingredient_id'  => $ingredientId,
                    'packaging_id'   => $packagingObj?->id,
                    'system_qty'     => round($sysQty, 4),
                    'physical_qty'   => round($physicalQty, 4),
                    'physical_crate' => $physicalCrate,
                    'physical_pack'  => $physicalPack,
                    'physical_base'  => $physicalBase,
                    'variance'       => round($physicalQty - $sysQty, 4),
                    'price_per_base' => $pricePerBase,
                ]);
            };

            // Satu baris per kemasan
            foreach ($allPackagings as $pkg) {
                $sysQty = round((float)($remainingByPkg[$pkg->id] ?? 0), 4);
                $saveItem($pkg->ingredient_id, $pkg, $sysQty, 'pkg_' . $pkg->id);
            }
            // Bahan tanpa kemasan
            foreach ($ingNoPkg as $ing) {
                $sysQty = round((float)($remainingByIng[$ing->id] ?? 0), 4);
                $saveItem($ing->id, null, $sysQty, 'ing_' . $ing->id);
            }
        });

        return redirect()->route('opname.opnames.show', $opname)
            ->with('success', 'Opname berhasil dibuat. Isi stok fisik lalu klik <strong>Approve</strong>.');
    }

    /**
     * API: ambil system_qty + harga rata-rata per bahan untuk store+date tertentu.
     * Dipakai oleh form buat opname (AJAX).
     */
    public function systemQty(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'date'     => 'required|date',
        ]);

        $storeId = $request->store_id;

        // Load semua kemasan aktif beserta bahan-nya
        $allPackagings  = IngredientPackaging::where('is_active', true)
            ->whereHas('ingredient', fn($q) => $q->where('type', '!=', 'semi_finished'))
            ->with('ingredient', 'supplier')
            ->orderBy('ingredient_id')->orderBy('id')
            ->get();

        $allIngredients = Ingredient::where('is_active', true)->where('type', '!=', 'semi_finished')
            ->leftJoin('ingredient_categories as ic', 'ingredients.category', '=', 'ic.name')
            ->orderByRaw('ic.sort_order IS NULL')->orderBy('ic.sort_order')->orderBy('ingredients.id')
            ->select('ingredients.*')->get();
        $ingIdsWithPkg  = $allPackagings->pluck('ingredient_id')->unique()->all();
        $pkgIds         = $allPackagings->pluck('id')->all();

        $opnameDate = $request->date;

        // ── System qty per kemasan ─────────────────────────────────────────────
        $remainingByPkg = $pkgIds
            ? MutationItem::whereHas('mutation', fn($q) =>
                    $q->where('destination_store_id', $storeId)->where('status', 'confirmed')
                      ->whereDate('transaction_date', '<=', $opnameDate)
                )
                ->whereIn('packaging_id', $pkgIds)
                ->selectRaw('packaging_id, SUM(remaining_qty) as total_remaining')
                ->groupBy('packaging_id')
                ->pluck('total_remaining', 'packaging_id')
            : collect();

        // ── Harga rata-rata per kemasan (weighted avg sisa batch = saldo stok) ─
        $batchesByPkg = $pkgIds
            ? MutationItem::whereHas('mutation', fn($q) =>
                    $q->where('destination_store_id', $storeId)
                      ->where('status', 'confirmed')
                      ->whereIn('type', ['purchase_zhisheng', 'purchase_supplier', 'opening_stock',
                                         'sale_internal', 'sale_external'])
                )
                ->whereIn('packaging_id', $pkgIds)
                ->where('remaining_qty', '>', 0)
                ->get(['packaging_id', 'ingredient_id', 'remaining_qty', 'price_per_base'])
                ->groupBy('packaging_id')
            : collect();

        // ── Bahan tanpa kemasan ────────────────────────────────────────────────
        $ingNoPkg = $allIngredients->filter(fn($i) => !in_array($i->id, $ingIdsWithPkg));

        $remainingByIng = $ingNoPkg->isNotEmpty()
            ? MutationItem::whereHas('mutation', fn($q) =>
                    $q->where('destination_store_id', $storeId)->where('status', 'confirmed')
                      ->whereDate('transaction_date', '<=', $opnameDate)
                )
                ->whereIn('ingredient_id', $ingNoPkg->pluck('id')->all())
                ->whereNull('packaging_id')
                ->selectRaw('ingredient_id, SUM(remaining_qty) as total_remaining')
                ->groupBy('ingredient_id')
                ->pluck('total_remaining', 'ingredient_id')
            : collect();

        $batchesByIng = $ingNoPkg->isNotEmpty()
            ? MutationItem::whereHas('mutation', fn($q) =>
                    $q->where('destination_store_id', $storeId)
                      ->where('status', 'confirmed')
                      ->whereIn('type', ['purchase_zhisheng', 'purchase_supplier', 'opening_stock',
                                         'sale_internal', 'sale_external'])
                )
                ->whereIn('ingredient_id', $ingNoPkg->pluck('id')->all())
                ->whereNull('packaging_id')
                ->where('remaining_qty', '>', 0)
                ->get(['ingredient_id', 'remaining_qty', 'price_per_base'])
                ->groupBy('ingredient_id')
            : collect();

        // ── Bangun result ──────────────────────────────────────────────────────
        // Tandai bahan yang punya > 1 kemasan supaya JS bisa tampilkan sub-label
        $pkgCountByIng = $allPackagings->groupBy('ingredient_id')->map->count();

        $result = [];

        // Satu entry per kemasan
        foreach ($allPackagings as $pkg) {
            $ing       = $pkg->ingredient;
            $isMulti   = ($pkgCountByIng[$ing->id] ?? 1) > 1;
            $sysQty    = round((float)($remainingByPkg[$pkg->id] ?? 0), 4);
            $group     = $batchesByPkg[$pkg->id] ?? collect();
            $totalQty  = $group->sum('remaining_qty');
            $totalVal  = $group->sum(fn($b) => $b->remaining_qty * $b->price_per_base);
            $priceBase = $totalQty > 0 ? round($totalVal / $totalQty, 6) : 0;
            $ctrPack   = (int)$pkg->crate_to_pack;
            $packBase  = (float)$pkg->pack_to_base;
            $priceDus  = ($ctrPack && $packBase) ? (int)round($priceBase * $ctrPack * $packBase) : 0;

            // Cantumkan nama supplier HANYA jika dari supplier lokal (bukan Zhisheng/pusat)
            $supLabel  = ($pkg->supplier && $pkg->supplier->type !== 'zhisheng') ? $pkg->supplier->name : null;
            $labelParts = array_filter([$isMulti ? '@ ' . $ctrPack . ' pack' : null, $supLabel]);

            $result[] = [
                'row_key'        => 'pkg_' . $pkg->id,
                'ingredient_id'  => $ing->id,
                'name'           => $ing->name,
                'unit_base'      => $ing->unit_base,
                'packaging_id'   => $pkg->id,
                'pkg_label'      => $labelParts ? implode(' · ', $labelParts) : null,
                'system_qty'     => $sysQty,
                'crate_to_pack'  => $ctrPack,
                'pack_to_base'   => $packBase,
                'price_per_base' => $priceBase,
                'price_per_dus'  => $priceDus,
            ];
        }

        // Bahan tanpa kemasan
        foreach ($ingNoPkg->sortBy('id') as $ing) {
            $sysQty    = round((float)($remainingByIng[$ing->id] ?? 0), 4);
            $group     = $batchesByIng[$ing->id] ?? collect();
            $totalQty  = $group->sum('remaining_qty');
            $totalVal  = $group->sum(fn($b) => $b->remaining_qty * $b->price_per_base);
            $priceBase = $totalQty > 0 ? round($totalVal / $totalQty, 6) : 0;

            $result[] = [
                'row_key'        => 'ing_' . $ing->id,
                'ingredient_id'  => $ing->id,
                'name'           => $ing->name,
                'unit_base'      => $ing->unit_base,
                'packaging_id'   => null,
                'pkg_label'      => null,
                'system_qty'     => $sysQty,
                'crate_to_pack'  => 0,
                'pack_to_base'   => 0,
                'price_per_base' => $priceBase,
                'price_per_dus'  => 0,
            ];
        }

        // Urutkan: nama bahan asc, lalu row_key asc (untuk multi-kemasan)
        usort($result, fn($a, $b) => $a['name'] <=> $b['name'] ?: $a['row_key'] <=> $b['row_key']);

        return response()->json($result);
    }

    public function show(Opname $opname)
    {
        $opname->load(['store', 'items.ingredient', 'items.packaging.supplier', 'performedBy', 'approvedBy']);
        $priceMap   = $this->buildPriceMap($opname);
        $lockData   = $this->buildLockData($opname);
        return view('opname.show', compact('opname', 'priceMap') + $lockData);
    }

    public function edit(Opname $opname)
    {
        abort_if($opname->status === 'approved', 403, 'Opname yang sudah approved tidak bisa diedit.');
        $opname->load(['items.ingredient', 'items.packaging.supplier', 'store', 'performedBy', 'approvedBy']);
        $priceMap = $this->buildPriceMap($opname);
        $lockData = $this->buildLockData($opname);
        return view('opname.show', compact('opname', 'priceMap') + $lockData);
    }

    private function buildLockData(Opname $opname): array
    {
        $month = $opname->period_month;
        $year  = $opname->period_year;
        return [
            'isLocked'   => MonthLockService::isLocked('opname', $opname->id, $month, $year),
            'isPastLock' => MonthLockService::isPastLock($month, $year),
            'hasPending' => UnlockRequest::hasPendingRequest('opname', $opname->id),
            'hasUnlock'  => UnlockRequest::hasApprovedUnlock('opname', $opname->id),
            'lockMonth'  => $month,
            'lockYear'   => $year,
        ];
    }

    /**
     * Harga rata-rata tertimbang per ingredient dari sisa batch FIFO —
     * sama persis dengan cara Saldo Stok menghitung harga.
     * Σ(remaining_qty × price_per_base) / Σ(remaining_qty)
     */
    private function buildPriceMap(Opname $opname): array
    {
        $ingIds  = $opname->items->pluck('ingredient_id')->unique()->all();
        $batches = MutationItem::whereHas('mutation', fn($q) =>
                $q->where('destination_store_id', $opname->store_id)
                  ->where('status', 'confirmed')
                  ->whereIn('type', ['purchase_zhisheng', 'purchase_supplier', 'opening_stock', 'sale_internal', 'sale_external'])
            )
            ->whereIn('ingredient_id', $ingIds)
            ->where('remaining_qty', '>', 0)
            ->get(['ingredient_id', 'remaining_qty', 'price_per_base'])
            ->groupBy('ingredient_id');

        $map = [];
        foreach ($ingIds as $id) {
            $group    = $batches[$id] ?? collect();
            $totalQty = $group->sum('remaining_qty');
            $totalVal = $group->sum(fn($b) => $b->remaining_qty * $b->price_per_base);
            $map[$id] = $totalQty > 0 ? $totalVal / $totalQty : 0;
        }
        return $map;
    }

    public function update(Request $request, Opname $opname)
    {
        abort_if($opname->status === 'approved', 403);

        // ── Lock check ──────────────────────────────────────────────────────────
        if (MonthLockService::isLocked('opname', $opname->id, $opname->period_month, $opname->period_year)) {
            return back()->with('error', MonthLockService::lockMessage($opname->period_month, $opname->period_year));
        }

        DB::transaction(function () use ($request, $opname) {
            foreach ($request->items as $itemId => $data) {
                $item = $opname->items()->find($itemId);
                if (!$item) continue;

                // Konversi fisik ke base unit (CTN + Pack + Pcs/Gr)
                $physicalQty = 0;
                if ($item->packaging_id && $item->packaging) {
                    $physicalQty = $item->packaging->convertToBase(
                        (int)($data['physical_crate'] ?? 0),
                        (int)($data['physical_pack']  ?? 0),
                        (float)($data['physical_base'] ?? 0)
                    );
                } else {
                    $physicalQty = (float)($data['physical_base'] ?? 0);
                }

                // Bulatkan ke 4 desimal untuk hindari floating-point noise
                $physicalQty = round($physicalQty, 4);
                $systemQty   = round($item->system_qty, 4);
                $variance    = round($physicalQty - $systemQty, 4);

                $item->update([
                    'physical_crate' => $data['physical_crate'] ?? null,
                    'physical_pack'  => $data['physical_pack']  ?? null,
                    'physical_base'  => $data['physical_base']  ?? null,
                    'physical_qty'   => $physicalQty,
                    'variance'       => $variance,
                ]);
            }
        });

        return back()->with('success', 'Stok fisik disimpan.');
    }

    /**
     * Hitung ulang variance untuk semua item (misal setelah fix floating point)
     */
    public function recalculate(Opname $opname)
    {
        abort_if($opname->status === 'approved', 403);

        if (MonthLockService::isLocked('opname', $opname->id, $opname->period_month, $opname->period_year)) {
            return back()->with('error', MonthLockService::lockMessage($opname->period_month, $opname->period_year));
        }
        foreach ($opname->items as $item) {
            $variance = round($item->physical_qty, 4) - round($item->system_qty, 4);
            $item->update(['variance' => round($variance, 4)]);
        }
        return back()->with('success', 'Variance berhasil dihitung ulang.');
    }

    public function destroy(Opname $opname)
    {
        // Approved hanya boleh dihapus oleh Super Admin
        if ($opname->status === 'approved' && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Opname yang sudah disetujui hanya dapat dihapus oleh Super Admin.');
        }

        if (MonthLockService::isLocked('opname', $opname->id, $opname->period_month, $opname->period_year)) {
            return redirect()->route('opname.opnames.show', $opname)
                ->with('error', MonthLockService::lockMessage($opname->period_month, $opname->period_year));
        }

        DB::transaction(function () use ($opname) {
            if ($opname->status === 'approved') {
                // ── Kumpulkan ingredient yang terdampak ────────────────────────
                $affectedIngIds = $opname->items->pluck('ingredient_id')->unique()->all();

                // ── Hapus stock_ledger adjustment dari opname ini ──────────────
                StockLedger::where('reference_type', 'Opname')
                    ->where('reference_id', $opname->id)
                    ->delete();

                // ── Hapus mutation bootstrap yang dibuat saat approve ──────────
                Mutation::where('type', 'opening_stock')
                    ->where('notes', 'Auto-generated dari Opname #' . $opname->id)
                    ->each(function ($m) {
                        $m->items()->delete();
                        $m->delete();
                    });

                // ── Recalculate FIFO & store_stocks untuk semua bahan terdampak ─
                foreach ($affectedIngIds as $ingId) {
                    FifoService::recalculate($opname->store_id, $ingId);
                }
            }

            $opname->items()->delete();
            $opname->delete();
        });

        return redirect()->route('opname.opnames.index')->with('success', 'Opname berhasil dihapus.');
    }

    public function approve(Opname $opname)
    {
        abort_if($opname->status === 'approved', 422, 'Opname sudah di-approve sebelumnya.');

        // ── Lock check ──────────────────────────────────────────────────────────
        if (MonthLockService::isLocked('opname', $opname->id, $opname->period_month, $opname->period_year)) {
            return back()->with('error', MonthLockService::lockMessage($opname->period_month, $opname->period_year));
        }

        DB::transaction(function () use ($opname) {
            $opname->update(['status' => 'approved', 'approved_by' => auth()->id()]);

            // ── Langkah 1: catat adjustment & recalculate untuk item yg punya selisih ──
            foreach ($opname->items as $item) {
                if ($item->variance == 0) continue;

                StockLedgerService::record(
                    $opname->store_id, $item->ingredient_id,
                    $opname->opname_date->format('Y-m-d'), 'opname_adjustment',
                    $item->variance, 'Opname', $opname->id,
                    "Opname adjustment"
                );

                FifoService::recalculate($opname->store_id, $item->ingredient_id);
            }

            // ── Langkah 2: bootstrap FIFO untuk item yang physical_qty > 0
            //    tapi saldo FIFO masih 0 setelah langkah di atas.
            //    Ini terjadi ketika tidak ada batch (data baru / data dihapus).
            //    Buat satu opening_stock mutation dari hasil opname agar saldo
            //    stok dan pencatatan harian bulan depan punya acuan yang benar.
            // ────────────────────────────────────────────────────────────────────
            // Set saldo FIFO setiap (bahan × KEMASAN) = jumlah fisik opname.
            // Selisih POSITIF → buat batch opening_stock per kemasan.
            // Selisih NEGATIF → ditangani FifoService::recalculate (step 7) per kemasan.
            $opening = null;
            foreach ($opname->items as $item) {
                if ($item->physical_qty <= 0) continue;

                // Saldo FIFO PER KEMASAN saat ini
                $curr = \App\Models\MutationItem::whereHas('mutation', fn($q) =>
                        $q->where('destination_store_id', $opname->store_id)->where('status', 'confirmed'))
                    ->where('ingredient_id', $item->ingredient_id)
                    ->when($item->packaging_id,
                        fn($q) => $q->where('packaging_id', $item->packaging_id),
                        fn($q) => $q->whereNull('packaging_id'))
                    ->sum('remaining_qty');

                $delta = round($item->physical_qty - $curr, 4);
                if ($delta <= 0) continue; // cukup / kekurangan (negatif ditangani step 7)

                // Harga: terakhir dari pembelian bahan ini; fallback ke harga manual opname
                $lastPrice = \App\Models\MutationItem::whereHas('mutation', fn($q) =>
                        $q->where('destination_store_id', $opname->store_id)->where('status', 'confirmed')
                          ->whereIn('type', ['purchase_zhisheng', 'purchase_supplier', 'opening_stock', 'sale_internal']))
                    ->where('ingredient_id', $item->ingredient_id)
                    ->where('packaging_id', $item->packaging_id)
                    ->where('price_per_base', '>', 0)
                    ->latest('id')->value('price_per_base') ?? 0;
                // Harga manual opname (mode Stok Awal) MENANG bila diisi; selain itu harga beli terakhir kemasan ini
                if ((float) $item->price_per_base > 0) {
                    $lastPrice = (float) $item->price_per_base;
                }

                $opening = $opening ?: \App\Models\Mutation::create([
                    'type'                 => 'opening_stock',
                    'destination_store_id' => $opname->store_id,
                    'transaction_date'     => $opname->opname_date,
                    'delivery_date'        => $opname->opname_date,
                    'status'               => 'confirmed',
                    'notes'                => 'Auto-generated dari Opname #' . $opname->id,
                    'created_by'           => auth()->id(),
                    'confirmed_by'         => auth()->id(),
                ]);

                $pkg         = $item->packaging;
                $crateToBase = ($pkg && $pkg->crate_to_pack && $pkg->pack_to_base) ? $pkg->crate_to_pack * $pkg->pack_to_base : 0;

                \App\Models\MutationItem::create([
                    'mutation_id'            => $opening->id,
                    'ingredient_id'          => $item->ingredient_id,
                    'packaging_id'           => $item->packaging_id,
                    'qty_crate'              => $crateToBase > 0 ? (int) floor($delta / $crateToBase) : 0,
                    'qty_pack'               => 0,
                    'qty_base'               => 0,
                    'total_in_base'          => $delta,
                    'remaining_qty'          => $delta,
                    'price_per_base'         => $lastPrice,
                    'selling_price_per_base' => 0,
                    'cost_subtotal'          => $delta * $lastPrice,
                ]);
            }

            // Sync FIFO & store_stocks sekali per bahan
            foreach ($opname->items->pluck('ingredient_id')->unique() as $iid) {
                FifoService::recalculate($opname->store_id, (int) $iid);
            }
        });

        return back()->with('success', 'Opname disetujui. Stok otomatis disesuaikan.');
    }

    // Export detail item satu opname
    public function export(Opname $opname)
    {
        $opname->load(['store', 'items.ingredient.packagings', 'performedBy']);

        $periodLabel = $opname->period_type === 'mid_month' ? 'Tengah Bulan' : 'Akhir Bulan';
        $monthLabel  = \Carbon\Carbon::create($opname->period_year, $opname->period_month)
                            ->isoFormat('MMMM Y');

        $data = [];
        $data[] = ["STOK OPNAME — {$opname->store->name} — {$monthLabel} ({$periodLabel})"];
        $data[] = ["Tgl Opname: {$opname->opname_date->format('d/m/Y')}",
                   "Status: {$opname->status}", "Dilakukan oleh: " . ($opname->performedBy?->name ?? '-')];
        $data[] = [];
        $data[] = ['No', 'Bahan', 'Satuan Base', 'Stok Sistem (base)',
                   'Stok Fisik (base)', 'Selisih (base)', 'Ket'];

        foreach ($opname->items as $i => $item) {
            $data[] = [
                $i + 1,
                $item->ingredient?->name ?? '-',
                $item->ingredient?->unit_base ?? '-',
                number_format($item->system_qty, 3, ',', '.'),
                number_format($item->physical_qty, 3, ',', '.'),
                number_format($item->variance, 3, ',', '.'),
                $item->variance > 0 ? 'Lebih' : ($item->variance < 0 ? 'Kurang' : 'Sesuai'),
            ];
        }

        $data[] = [];
        $data[] = ['', 'TOTAL ITEM', '', '', '', '', $opname->items->count() . ' bahan'];

        $filename = "opname_{$opname->store->name}_{$opname->period_year}-{$opname->period_month}.xlsx";
        return Excel::download(new ArrayExport($data), $filename);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  TEMPLATE DOWNLOAD
    // ═══════════════════════════════════════════════════════════════════════
    public function downloadTemplate(Request $request)
    {
        $request->validate([
            'store_id'    => 'required|exists:stores,id',
            'date'        => 'required|date',
            'period_type' => 'required|in:mid_month,end_month',
            'opname_mode' => 'nullable|in:bulanan,stok_awal',
        ]);

        $storeId    = (int)$request->store_id;
        $date       = $request->date;
        $periodType = $request->period_type;
        $opnameMode = $request->input('opname_mode', 'bulanan');
        $store      = \App\Models\Store::findOrFail($storeId);

        // Ambil data bahan + sistem qty (sama seperti API systemQty)
        $apiReq  = new \Illuminate\Http\Request(['store_id' => $storeId, 'date' => $date]);
        $apiResp = $this->systemQty($apiReq);
        $rows    = json_decode($apiResp->getContent(), true);

        // ── Build Spreadsheet ───────────────────────────────────────────────
        $ss   = new Spreadsheet();
        $ws   = $ss->getActiveSheet();
        $ws->setTitle('Opname');

        $periodLabel = $periodType === 'mid_month' ? 'Tengah Bulan (1-15)' : 'Akhir Bulan (1-30/31)';
        $carbon      = \Carbon\Carbon::parse($date);

        // Baris 1: metadata (jangan diubah)
        $ws->setCellValue('A1', 'METADATA');
        $ws->setCellValue('B1', $storeId);
        $ws->setCellValue('C1', $date);
        $ws->setCellValue('D1', $periodType);
        $ws->setCellValue('E1', $opnameMode);
        $ws->getRowDimension(1)->setRowHeight(14);

        // Baris 2: judul
        $modeLabel = $opnameMode === 'stok_awal' ? ' [STOK AWAL]' : '';
        $ws->setCellValue('A2', "TEMPLATE STOK OPNAME — {$store->name} — {$carbon->format('d/m/Y')} — {$periodLabel}{$modeLabel}");
        $ws->mergeCells('A2:I2');
        $ws->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $ws->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Baris 3: header — hanya kuantitas stok, tanpa kolom harga
        $headers = ['Row Key (jgn ubah)', 'Nama Bahan', 'Kemasan',
                    'Sistem (Dus)', 'Sistem (Pack)',
                    'FISIK Dus ✏', 'FISIK Pack ✏', 'FISIK Pcs/Gr ✏', 'Catatan'];
        foreach ($headers as $col => $h) {
            $ws->setCellValueByColumnAndRow($col + 1, 3, $h);
        }

        // Style header baris 3
        $ws->getStyle('A3:I3')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '1e3a5f']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Baris data mulai baris 4
        $rowNum = 4;
        foreach ($rows as $r) {
            $crateToBase = $r['crate_to_pack'] * $r['pack_to_base'];
            $sysDus  = $crateToBase > 0 ? floor($r['system_qty'] / $crateToBase) : 0;
            $sysPack = $crateToBase > 0
                ? floor(($r['system_qty'] - $sysDus * $crateToBase) / $r['pack_to_base'])
                : floor($r['system_qty'] / max($r['pack_to_base'], 1));

            $ws->setCellValueByColumnAndRow(1, $rowNum, $r['row_key']);
            $ws->setCellValueByColumnAndRow(2, $rowNum, $r['name'] . ($r['pkg_label'] ? '  ' . $r['pkg_label'] : ''));
            $ws->setCellValueByColumnAndRow(3, $rowNum, $r['pkg_label'] ?? ($r['unit_base'] ?? '-'));
            $ws->setCellValueByColumnAndRow(4, $rowNum, $sysDus);
            $ws->setCellValueByColumnAndRow(5, $rowNum, $sysPack);
            $ws->setCellValueByColumnAndRow(6, $rowNum, '');  // Fisik Dus
            $ws->setCellValueByColumnAndRow(7, $rowNum, '');  // Fisik Pack
            $ws->setCellValueByColumnAndRow(8, $rowNum, '');  // Fisik Pcs/Gr
            $ws->setCellValueByColumnAndRow(9, $rowNum, '');  // Catatan

            // Style baris data
            $ws->getStyle("A{$rowNum}:E{$rowNum}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F2F2F2']],
                'font' => ['color' => ['rgb' => '888888']],
            ]);
            // Editable: Fisik Dus/Pack/Pcs (F-H)
            $ws->getStyle("F{$rowNum}:H{$rowNum}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFDE7']],
                'font' => ['bold' => true],
            ]);
            $rowNum++;
        }

        // Column widths
        $ws->getColumnDimension('A')->setWidth(18);
        $ws->getColumnDimension('B')->setWidth(32);
        $ws->getColumnDimension('C')->setWidth(16);
        $ws->getColumnDimension('D')->setWidth(14);
        $ws->getColumnDimension('E')->setWidth(14);
        $ws->getColumnDimension('F')->setWidth(14);
        $ws->getColumnDimension('G')->setWidth(14);
        $ws->getColumnDimension('H')->setWidth(14);
        $ws->getColumnDimension('I')->setWidth(24);

        // Style metadata row 1 (kecil & abu)
        $ws->getStyle('A1:E1')->applyFromArray([
            'font' => ['size' => 8, 'color' => ['rgb' => 'AAAAAA']],
        ]);

        // Freeze pane
        $ws->freezePane('F4');

        $filename = 'template_opname_' . $store->name . '_' . $carbon->format('Ymd') . '.xlsx';
        $writer   = new XlsxWriter($ss);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  IMPORT FORM
    // ═══════════════════════════════════════════════════════════════════════
    public function importForm()
    {
        $stores = auth()->user()->accessibleStores();
        return view('opname.import', compact('stores'));
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  IMPORT PROCESS
    // ═══════════════════════════════════════════════════════════════════════
    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls|max:5120']);

        $path = $request->file('file')->getRealPath();
        $ss   = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        $ws   = $ss->getActiveSheet();

        // ── Baca metadata baris 1 ────────────────────────────────────────────
        if ($ws->getCell('A1')->getValue() !== 'METADATA') {
            return back()->withErrors(['file' => 'File tidak valid: pastikan menggunakan template yang benar.']);
        }
        $storeId    = (int)$ws->getCell('B1')->getValue();
        $date       = $ws->getCell('C1')->getValue();
        $periodType = $ws->getCell('D1')->getValue();
        $opnameMode = $ws->getCell('E1')->getValue() ?: 'bulanan';
        if (!in_array($opnameMode, ['bulanan', 'stok_awal'])) $opnameMode = 'bulanan';

        // Validasi metadata
        $storeIds = auth()->user()->accessibleStoreIds();
        if (!in_array($storeId, $storeIds)) {
            return back()->withErrors(['file' => 'Toko tidak ditemukan atau tidak dapat diakses.']);
        }
        if (!in_array($periodType, ['mid_month', 'end_month'])) {
            return back()->withErrors(['file' => 'Tipe periode tidak valid.']);
        }

        $carbon = \Carbon\Carbon::parse($date);
        $month  = $carbon->month;
        $year   = $carbon->year;

        // Cek opname-based period lock untuk store ini
        if (Opname::isDateLocked($storeId, $date)) {
            return back()->withErrors(['file' => Opname::lockMessageFor($storeId)]);
        }

        // Cek duplikat
        if (Opname::where('store_id', $storeId)->where('period_month', $month)
                  ->where('period_year', $year)->where('period_type', $periodType)->exists()) {
            return back()->withErrors(['file' => "Opname periode {$month}/{$year} ({$periodType}) untuk toko ini sudah ada."]);
        }

        // ── Baca baris data (mulai baris 4) ─────────────────────────────────
        $errors = [];
        $items  = [];
        $rowNum = 4;
        $maxRow = $ws->getHighestDataRow();

        while ($rowNum <= $maxRow) {
            $rowKey  = trim((string)$ws->getCellByColumnAndRow(1, $rowNum)->getValue());
            if ($rowKey === '') { $rowNum++; continue; }

            $fisikDus  = $ws->getCellByColumnAndRow(6, $rowNum)->getValue();
            $fisikPack = $ws->getCellByColumnAndRow(7, $rowNum)->getValue();
            $fisikBase = $ws->getCellByColumnAndRow(8, $rowNum)->getValue();

            // Validasi nilai
            foreach ([
                "Baris {$rowNum} Fisik Dus"    => $fisikDus,
                "Baris {$rowNum} Fisik Pack"   => $fisikPack,
                "Baris {$rowNum} Fisik Pcs/Gr" => $fisikBase,
            ] as $label => $val) {
                if ($val !== '' && $val !== null && (!is_numeric($val) || (float)$val < 0)) {
                    $errors[] = "{$label}: nilai harus angka ≥ 0 (dapat dikosongkan).";
                }
            }

            $items[] = [
                'row_key'       => $rowKey,
                'physical_crate' => $fisikDus  !== '' && $fisikDus  !== null ? (int)$fisikDus  : null,
                'physical_pack'  => $fisikPack !== '' && $fisikPack !== null ? (int)$fisikPack : null,
                'physical_base'  => $fisikBase !== '' && $fisikBase !== null ? (float)$fisikBase : null,
            ];
            $rowNum++;
        }

        if (!empty($errors)) {
            return back()->withErrors(['file' => implode(' | ', $errors)]);
        }
        if (empty($items)) {
            return back()->withErrors(['file' => 'Tidak ada data bahan dalam file.']);
        }

        // ── Ambil data sistem qty untuk semua row_key ────────────────────────
        $apiReq  = new \Illuminate\Http\Request(['store_id' => $storeId, 'date' => $date]);
        $apiData = json_decode($this->systemQty($apiReq)->getContent(), true);
        $sysMap  = collect($apiData)->keyBy('row_key');

        // ── Simpan opname ────────────────────────────────────────────────────
        $opname = null;
        DB::transaction(function () use (
            $storeId, $date, $month, $year, $periodType, $items, $sysMap, &$opname
        ) {
            $opname = Opname::create([
                'store_id'     => $storeId,
                'opname_date'  => $date,
                'period_month' => $month,
                'period_year'  => $year,
                'period_type'  => $periodType,
                'status'       => 'draft',
                'performed_by' => auth()->id(),
                'notes'        => 'Import dari Excel',
            ]);

            foreach ($items as $item) {
                $sys = $sysMap->get($item['row_key']);
                if (!$sys) continue;

                $ingId  = $sys['ingredient_id'];
                $pkgId  = $sys['packaging_id'];
                $sysQty = (float)$sys['system_qty'];
                $pkg    = $pkgId ? IngredientPackaging::find($pkgId) : null;

                $c = $item['physical_crate'];
                $p = $item['physical_pack'];
                $b = $item['physical_base'];

                if ($pkg && ($c !== null || $p !== null || $b !== null)) {
                    $physQty = $pkg->convertToBase($c ?? 0, $p ?? 0, $b ?? 0);
                } elseif ($b !== null) {
                    $physQty = $b;
                } else {
                    $physQty = 0;
                }

                OpnameItem::create([
                    'opname_id'      => $opname->id,
                    'ingredient_id'  => $ingId,
                    'packaging_id'   => $pkgId,
                    'system_qty'     => round($sysQty, 4),
                    'physical_qty'   => round($physQty, 4),
                    'physical_crate' => $c,
                    'physical_pack'  => $p,
                    'physical_base'  => $b,
                    'variance'       => round($physQty - $sysQty, 4),
                ]);
            }
        });

        return redirect()->route('opname.opnames.show', $opname)
            ->with('success', 'Opname berhasil diimport. Periksa data lalu klik Approve.');
    }
}