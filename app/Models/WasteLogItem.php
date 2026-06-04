<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WasteLogItem extends Model
{
    protected $fillable = [
        'waste_log_id', 'source_type', 'source_ingredient_id', 'source_qty',
        'packaging_id', 'qty_crate', 'qty_pack',
        'ingredient_id', 'qty_base', 'price_per_base', 'subtotal_loss', 'is_rework',
    ];

    protected $casts = ['is_rework' => 'boolean'];

    public function packaging() { return $this->belongsTo(\App\Models\IngredientPackaging::class); }
    public function wasteLog()          { return $this->belongsTo(WasteLog::class); }
    public function sourceIngredient()  { return $this->belongsTo(Ingredient::class, 'source_ingredient_id'); }
    public function ingredient()        { return $this->belongsTo(Ingredient::class); }
}
