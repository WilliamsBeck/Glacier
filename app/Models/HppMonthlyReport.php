<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HppMonthlyReport extends Model
{
    protected $fillable = [
        'store_id', 'ingredient_id', 'month', 'year', 'period_type',
        'date_from', 'date_to', 'ideal_usage_base', 'actual_usage_base',
        'avg_price_per_base', 'hpp_ideal', 'hpp_actual',
        'variance_qty', 'variance_amount',
    ];
    protected $casts = ['date_from' => 'date', 'date_to' => 'date'];
    public function store()      { return $this->belongsTo(Store::class); }
    public function ingredient() { return $this->belongsTo(Ingredient::class); }
}
