<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    protected $fillable = ['menu_id', 'store_id', 'recipe_group_id', 'ingredient_id', 'qty_usage', 'unit', 'effective_from', 'created_by'];

    protected $casts = ['effective_from' => 'date'];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
