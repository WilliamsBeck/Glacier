<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = ['name', 'category', 'category_id', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function menuCategory()
    {
        return $this->belongsTo(MenuCategory::class, 'category_id');
    }

    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }

    public function sales()
    {
        return $this->hasMany(MonthlySale::class);
    }

    // Ambil resep aktif per tanggal tertentu
    public function activeRecipes(string $date)
    {
        return $this->recipes()
            ->where('effective_from', '<=', $date)
            ->orderByDesc('effective_from')
            ->get()
            ->groupBy('ingredient_id')
            ->map(fn($group) => $group->first());
    }
}
