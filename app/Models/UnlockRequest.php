<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnlockRequest extends Model
{
    protected $fillable = [
        'resource_type',
        'resource_id',
        'store_id',
        'resource_month',
        'resource_year',
        'resource_period_type',
        'requested_by',
        'reason',
        'status',
        'reviewed_by',
        'reviewed_at',
        'admin_notes',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    // ── Relations ──────────────────────────────────────────────────────────────
    public function store()       { return $this->belongsTo(Store::class); }
    public function requestedBy() { return $this->belongsTo(User::class, 'requested_by'); }
    public function reviewedBy()  { return $this->belongsTo(User::class, 'reviewed_by'); }

    // ── Helpers ────────────────────────────────────────────────────────────────
    public function isPending()  { return $this->status === 'pending'; }
    public function isApproved() { return $this->status === 'approved'; }
    public function isRejected() { return $this->status === 'rejected'; }

    /** Label tipe resource untuk tampilan */
    public function resourceLabel(): string
    {
        return match($this->resource_type) {
            'mutation'     => 'Mutasi Stok',
            'opname'       => 'Stock Opname',
            'monthly_sale' => 'Data Penjualan',
            default        => $this->resource_type,
        };
    }

    /** Cek apakah unlock request ini masih aktif (status approved) */
    public function isActive(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Scope: cek apakah ada approved unlock untuk resource tertentu.
     * Dipakai oleh MonthLockService.
     */
    public static function hasApprovedUnlock(string $type, int $resourceId): bool
    {
        return self::where('resource_type', $type)
            ->where('resource_id', $resourceId)
            ->where('status', 'approved')
            ->exists();
    }

    /**
     * Scope: cek apakah ada pending request untuk resource tertentu.
     */
    public static function hasPendingRequest(string $type, int $resourceId): bool
    {
        return self::where('resource_type', $type)
            ->where('resource_id', $resourceId)
            ->where('status', 'pending')
            ->exists();
    }
}
