<?php
namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\{UnlockRequest, Mutation, Opname, MonthlySale, MonthlyRevenue};
use App\Services\MonthLockService;
use Illuminate\Http\Request;

class UnlockRequestController extends Controller
{
    // ── User: ajukan request unlock per record ─────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'resource_type' => 'required|in:mutation,opname,monthly_sale',
            'resource_id'   => 'nullable|integer|min:1',
            'store_id'      => 'required|exists:stores,id',
            'resource_month'=> 'required|integer|between:1,12',
            'resource_year' => 'required|integer|min:2020',
            'resource_period_type' => 'nullable|in:end_month,mid_month',
            'reason'        => 'required|string|max:1000',
        ]);

        abort_unless(in_array($request->store_id, auth()->user()->accessibleStoreIds()), 403);

        $type = $request->resource_type;

        // Untuk monthly_sale: cari MonthlyRevenue sebagai anchor resource_id
        $rid = $request->filled('resource_id') ? (int)$request->resource_id : null;
        if ($type === 'monthly_sale' && !$rid) {
            $rev = MonthlyRevenue::where([
                'store_id'    => $request->store_id,
                'month'       => $request->resource_month,
                'year'        => $request->resource_year,
                'period_type' => $request->resource_period_type ?? 'end_month',
            ])->first();
            $rid = $rev?->id; // nullable jika belum ada omset
        }

        // Cek kalau belum melewati lock — tidak perlu request
        if (!MonthLockService::isPastLock((int)$request->resource_month, (int)$request->resource_year)) {
            return back()->with('error', 'Data belum terkunci, tidak perlu request unlock.');
        }

        // Cek duplikat pending (hanya jika ada resource_id)
        if ($rid && UnlockRequest::hasPendingRequest($type, $rid)) {
            return back()->with('error', 'Request unlock untuk data ini sudah ada dan masih menunggu persetujuan.');
        }

        // Cek apakah sudah approved (hanya jika ada resource_id)
        if ($rid && UnlockRequest::hasApprovedUnlock($type, $rid)) {
            return back()->with('error', 'Data ini sudah di-unlock oleh Super Admin.');
        }

        UnlockRequest::create([
            'resource_type'         => $type,
            'resource_id'           => $rid,
            'store_id'              => $request->store_id,
            'resource_month'        => $request->resource_month,
            'resource_year'         => $request->resource_year,
            'resource_period_type'  => $request->resource_period_type,
            'requested_by'          => auth()->id(),
            'reason'                => $request->reason,
        ]);

        return back()->with('success', 'Request unlock berhasil diajukan. Menunggu persetujuan Super Admin.');
    }

    // ── Super Admin: daftar semua request ─────────────────────────────────────
    public function index(Request $request)
    {
        $query = UnlockRequest::with(['store', 'requestedBy', 'reviewedBy'])
            ->latest();

        if ($request->status)        $query->where('status', $request->status);
        if ($request->resource_type) $query->where('resource_type', $request->resource_type);

        $requests     = $query->paginate(20);
        $pendingCount = UnlockRequest::where('status', 'pending')->count();

        return view('inventory.unlock-requests.index', compact('requests', 'pendingCount'));
    }

    // ── Super Admin: approve ───────────────────────────────────────────────────
    public function approve(Request $request, UnlockRequest $unlockRequest)
    {
        abort_if(!$unlockRequest->isPending(), 422, 'Request sudah diproses.');

        $request->validate(['admin_notes' => 'nullable|string|max:500']);

        $unlockRequest->update([
            'status'      => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        return back()->with('success', 'Request unlock disetujui.');
    }

    // ── Super Admin: reject ────────────────────────────────────────────────────
    public function reject(Request $request, UnlockRequest $unlockRequest)
    {
        abort_if(!$unlockRequest->isPending(), 422, 'Request sudah diproses.');

        $request->validate(['admin_notes' => 'required|string|max:500']);

        $unlockRequest->update([
            'status'      => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        return back()->with('success', 'Request unlock ditolak.');
    }
}
