<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IngredientComposition extends Model
{
    protected $fillable = ['parent_id', 'child_id', 'qty_needed', 'qty_raw', 'qty_output'];

    // Hitung rasio presisi tinggi di runtime (hindari pembulatan decimal DB)
    public function getQtyNeededExactAttribute(): float
    {
        if ($this->qty_output > 0) {
            return (float) $this->qty_raw / (float) $this->qty_output;
        }
        return (float) $this->qty_needed;
    }

    public function parent()
    {
        return $this->belongsTo(Ingredient::class, 'parent_id');
    }

    public function child()
    {
        return $this->belongsTo(Ingredient::class, 'child_id');
    }
}
