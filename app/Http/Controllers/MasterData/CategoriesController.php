<?php
namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\IngredientCategory;
use App\Models\MenuCategory;

class CategoriesController extends Controller
{
    public function index()
    {
        $ingredientCategories = IngredientCategory::ordered()->get();
        $menuCategories       = MenuCategory::ordered()->get();
        return view('master.categories.index', compact('ingredientCategories', 'menuCategories'));
    }
}
