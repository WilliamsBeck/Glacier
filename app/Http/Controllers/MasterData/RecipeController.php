<?php
namespace App\Http\Controllers\MasterData;
use App\Http\Controllers\Controller;
use App\Models\{Recipe, Menu, Ingredient, Store};
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    public function index(Request $request)
    {
        $query = Recipe::with(['menu', 'store', 'ingredient', 'createdBy']);
        if ($request->menu_id)  $query->where('menu_id', $request->menu_id);
        if ($request->store_id) $query->where('store_id', $request->store_id === 'default' ? null : $request->store_id);
        $recipes = $query->latest()->paginate(20);
        $menus   = Menu::where('is_active', true)->orderBy('name')->get();
        $stores  = Store::where('is_active', true)->orderBy('name')->get();
        return view('master.recipes.index', compact('recipes', 'menus', 'stores'));
    }
    public function create()
    {
        $menus       = Menu::where('is_active', true)->orderBy('name')->get();
        $ingredients = Ingredient::where('ingredients.is_active', true)->orderedByCategory()->get();
        $stores      = Store::where('is_active', true)->orderBy('name')->get();
        return view('master.recipes.form', compact('menus', 'ingredients', 'stores'));
    }
    public function duplicate(Recipe $recipe)
    {
        // Semua resep dalam 1 versi share recipe_group_id (set bahan sama untuk N toko)
        $sourceItems = Recipe::where('recipe_group_id', $recipe->recipe_group_id)
            ->with('ingredient')->get()
            ->unique('ingredient_id')->values();

        $menus       = Menu::where('is_active', true)->orderBy('name')->get();
        $ingredients = Ingredient::where('ingredients.is_active', true)->orderedByCategory()->get();
        $stores      = Store::where('is_active', true)->orderBy('name')->get();

        return view('master.recipes.form', compact('menus', 'ingredients', 'stores', 'sourceItems', 'recipe'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'menu_id'               => 'required|exists:menus,id',
            'store_ids'             => 'nullable|array',
            'store_ids.*'           => 'exists:stores,id',
            'effective_from'        => 'required|date',
            'items'                 => 'required|array|min:1',
            'items.*.ingredient_id' => 'required|exists:ingredients,id',
            'items.*.qty_usage'     => 'required|numeric|min:0.001',
            'items.*.unit'          => 'required|string',
        ]);
        $storeIds = $request->input('store_ids', []);
        if (empty($storeIds)) $storeIds = [null]; // tidak ada ceklis = default semua toko
        $groupId  = (string) \Illuminate\Support\Str::uuid();

        foreach ($storeIds as $sid) {
            foreach ($request->items as $item) {
                Recipe::create([
                    'menu_id'         => $request->menu_id,
                    'store_id'        => $sid ?: null,
                    'recipe_group_id' => $groupId,
                    'ingredient_id'   => $item['ingredient_id'],
                    'qty_usage'       => $item['qty_usage'],
                    'unit'            => $item['unit'],
                    'effective_from'  => $request->effective_from,
                    'created_by'      => auth()->id(),
                ]);
            }
        }
        return redirect()->route('master.recipes.index')->with('success', 'Resep disimpan.');
    }
}
