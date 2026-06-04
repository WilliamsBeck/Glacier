<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IngredientCategory extends Model
{
    protected $fillable = ['name', 'label', 'sort_order'];

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /** Ambil array ['solid','bubuk',...] untuk validasi & urutan */
    public static function orderedNames(): array
    {
        return static::ordered()->pluck('name')->all();
    }

    /** Ambil map ['solid'=>'Solid',...] untuk label tampilan */
    public static function labelsMap(): array
    {
        return static::ordered()->pluck('label', 'name')->all();
    }
}
