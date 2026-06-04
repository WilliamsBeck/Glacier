<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IngredientPackaging extends Model
{
    protected $fillable = ['ingredient_id', 'supplier_id', 'packaging_name', 'crate_to_pack', 'pack_to_base', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // Konversi qty ke satuan dasar
    public function convertToBase(int $crate = 0, int $pack = 0, float $base = 0): float
    {
        return ($crate * $this->crate_to_pack * $this->pack_to_base)
             + ($pack * $this->pack_to_base)
             + $base;
    }
}
