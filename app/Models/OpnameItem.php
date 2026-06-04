<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpnameItem extends Model
{
    protected $fillable = [
        'opname_id', 'ingredient_id', 'packaging_id',
        'system_qty', 'physical_crate', 'physical_pack',
        'physical_base', 'physical_qty', 'variance', 'price_per_base',
    ];
    public function opname()     { return $this->belongsTo(Opname::class); }
    public function ingredient() { return $this->belongsTo(Ingredient::class); }
    public function packaging()  { return $this->belongsTo(IngredientPackaging::class); }
}
