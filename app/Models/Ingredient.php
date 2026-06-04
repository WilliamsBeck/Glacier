<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    protected $fillable = ['name', 'type', 'category', 'unit_base', 'is_active'];

    const CATEGORIES = ['bubuk', 'teh', 'sirup', 'selai', 'solid', 'kemasan'];
    const CATEGORY_ORDER = ['solid', 'bubuk', 'teh', 'sirup', 'selai', 'kemasan'];

    protected $casts = ['is_active' => 'boolean'];

    public function packagings()
    {
        return $this->hasMany(IngredientPackaging::class);
    }

    // Bahan pembentuk (parent = semi_finished, child = raw)
    public function compositions()
    {
        return $this->hasMany(IngredientComposition::class, 'parent_id');
    }

    public function compositionParents()
    {
        return $this->hasMany(IngredientComposition::class, 'child_id');
    }

    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }

    public function isRaw(): bool
    {
        return $this->type === 'raw';
    }

    public function isSemiFinished(): bool
    {
        return $this->type === 'semi_finished';
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'raw' ? 'Bahan Baku' : 'Setengah Jadi';
    }
}
