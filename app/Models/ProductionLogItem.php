<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionLogItem extends Model
{
    protected $fillable = ['production_log_id', 'raw_ingredient_id', 'qty_consumed', 'price_per_base'];

    public function productionLog()  { return $this->belongsTo(ProductionLog::class); }
    public function rawIngredient()  { return $this->belongsTo(Ingredient::class, 'raw_ingredient_id'); }
}
