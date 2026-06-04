<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyUsage extends Model
{
    protected $fillable = ['store_id', 'ingredient_id', 'packaging_id', 'usage_date', 'qty_pack', 'created_by'];
    protected $casts    = ['usage_date' => 'date'];

    public function store()      { return $this->belongsTo(Store::class); }
    public function ingredient() { return $this->belongsTo(Ingredient::class); }
}
