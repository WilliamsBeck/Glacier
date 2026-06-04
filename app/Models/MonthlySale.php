<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlySale extends Model
{
    protected $fillable = ['store_id', 'menu_id', 'total_sold', 'total_revenue', 'month', 'year', 'period_type', 'recorded_by'];
    public function store()      { return $this->belongsTo(Store::class); }
    public function menu()       { return $this->belongsTo(Menu::class); }
    public function recordedBy() { return $this->belongsTo(User::class, 'recorded_by'); }
}
