<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyEditRequest extends Model
{
    protected $fillable = [
        'store_id', 'request_month', 'request_year',
        'requested_by', 'reason', 'extra_days',
        'status', 'reviewed_by', 'reviewed_at',
        'new_lock_until', 'admin_notes',
    ];
    protected $casts = [
        'reviewed_at'    => 'datetime',
        'new_lock_until' => 'date',
    ];

    public function store()       { return $this->belongsTo(Store::class); }
    public function requestedBy() { return $this->belongsTo(User::class, 'requested_by'); }
    public function reviewedBy()  { return $this->belongsTo(User::class, 'reviewed_by'); }

    public function isPending():  bool { return $this->status === 'pending'; }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }
}
