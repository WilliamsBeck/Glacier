<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyRevenue extends Model
{
    protected $fillable = ['store_id', 'month', 'year', 'period_type', 'total_revenue', 'recorded_by'];

    public function store()      { return $this->belongsTo(Store::class); }
    public function recordedBy() { return $this->belongsTo(User::class, 'recorded_by'); }
}
