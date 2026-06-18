<?php
namespace App\Http\Controllers\MasterData;
use App\Http\Controllers\Controller;
use App\Models\{Menu, MenuCategory, Recipe, Ingredient, Store};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $query = Menu::withCount('recipes')
            ->withCount(['recipes as recipe_versions_count' => function ($q) {
                $q->select(DB::raw('COUNT(DISTINCT effective_from)'));
            }])
            ->leftJoin('menu_categories as mc', 'menus.category_id', '=', 'mc.id')
            ->select('menus.*');

        if ($request->search)      $query->where('menus.name', 'like', "%{$request->search}%");
        if ($request->category_id) $query->where('menus.category_id', $request->category_id);

        $query->orderByRaw('mc.sort_order IS NULL')
              ->orderBy('mc.sort_order')
              ->orderBy('menus.id');

        $menus          = $query->paginate(20)->withQueryString();
        $menuCategories = MenuCategory::ordered()->get();
        return view('master.menus.index', compact('menus', 'menuCategories'));
    }

    public function create()
    {
        $ingredients     = Ingredient::where('is_active', true)->orderBy('name')->get();
        $menuCategories  = MenuCategory::ordered()->get();
        $stores          = Store::where('is_active', true)->orderBy('name')->get();
        return view('master.menus.form', compact('ingredients', 'menuCategories', 'stores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                      => 'required|string',
            'category'                  => 'nullable|string',
            'items.*.ingredient_id'     => 'nullable|exists:ingredients,id',
            'items.*.qty_usage'         => 'nullable|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {
            $menu = Menu::create([
                'name'        => $request->name,
                'category'    => $request->category,
                'category_id' => $request->category_id ?: null,
                'is_active'   => $request->has('is_active'),
            ]);

            $effectiveFrom = $request->effective_from ?? now()->toDateString();
            foreach ($request->input('items', []) as $item) {
                if (empty($item['ingredient_id']) || empty($item['qty_usage'])) continue;
                Recipe::create([
                    'menu_id'        => $menu->id,
                    'ingredient_id'  => $item['ingredient_id'],
                    'qty_usage'      => $item['qty_usage'],
                    'unit'           => $item['unit'] ?? 'gram',
                    'effective_from' => $effectiveFrom,
                    'created_by'     => auth()->id(),
                ]);
            }
        });

        return redirect()->route('master.menus.index')->with('success', 'Menu dan resep berhasil disimpan.');
    }

    public function show(Menu $menu)
    {
        $menu->load(['recipes.ingredient', 'recipes.store']);
        $recipes = $menu->recipes()->orderByDesc('effective_from')->get()->groupBy('recipe_group_id');
        return view('master.menus.show', compact('menu', 'recipes'));
    }
    public function edit(Menu $menu)   { return $this->renderForm($menu); }

    private function renderForm(Menu $menu)
    {
        $menu->load(['recipes.ingredient', 'recipes.store']);
        // 1 versi = 1 recipe_group_id (banyak toko share resep yang sama)
        $recipes        = $menu->recipes()->orderByDesc('effective_from')->get()
            ->groupBy('recipe_group_id');
        $ingredients    = Ingredient::where('is_active', true)->orderBy('name')->get();
        $menuCategories = MenuCategory::ordered()->get();
        $stores         = Store::where('is_active', true)->orderBy('name')->get();
        return view('master.menus.form', compact('menu', 'recipes', 'ingredients', 'menuCategories', 'stores'));
    }

    public function update(Request $request, Menu $menu)
    {
        $request->validate([
            'name'                      => 'required|string',
            'category'                  => 'nullable|string',
            'items.*.ingredient_id'     => 'nullable|exists:ingredients,id',
            'items.*.qty_usage'         => 'nullable|integer|min:1',
        ]);

        DB::transaction(function () use ($request, $menu) {
            $menu->update([
                'name'        => $request->name,
                'category'    => $request->category,
                'category_id' => $request->category_id ?: null,
                'is_active'   => $request->has('is_active'),
            ]);

            // Simpan versi resep baru jika ada item yang diisi
            $hasItems = collect($request->input('items', []))
                ->filter(fn($i) => !empty($i['ingredient_id']) && !empty($i['qty_usage']))
                ->count() > 0;

            if ($hasItems) {
                $effectiveFrom = $request->effective_from ?? now()->toDateString();
                // store_ids = array toko (kosong = berlaku default semua toko = [null])
                $storeIds = $request->input('store_ids', []);
                if (empty($storeIds)) $storeIds = [null];
                else $storeIds = array_map(fn($s) => (int) $s, $storeIds);
                $groupId = (string) \Illuminate\Support\Str::uuid();

                // Hapus resep lama yg overlap (effective_from + store_id target)
                $menu->recipes()
                    ->where('effective_from', $effectiveFrom)
                    ->where(function ($q) use ($storeIds) {
                        if (in_array(null, $storeIds, true)) $q->orWhereNull('store_id');
                        $non = array_filter($storeIds, fn($s) => $s !== null);
                        if (!empty($non)) $q->orWhereIn('store_id', $non);
                    })
                    ->delete();

                foreach ($storeIds as $sid) {
                    foreach ($request->input('items', []) as $item) {
                        if (empty($item['ingredient_id']) || empty($item['qty_usage'])) continue;
                        Recipe::create([
                            'menu_id'         => $menu->id,
                            'store_id'        => $sid,
                            'recipe_group_id' => $groupId,
                            'ingredient_id'   => $item['ingredient_id'],
                            'qty_usage'       => $item['qty_usage'],
                            'unit'            => $item['unit'] ?? 'gram',
                            'effective_from'  => $effectiveFrom,
                            'created_by'      => auth()->id(),
                        ]);
                    }
                }
            }
        });

        return redirect()->route('master.menus.edit', $menu)->with('success', 'Menu berhasil diupdate.');
    }

    public function destroy(Menu $menu)
    {
        $hasData = \App\Models\MonthlySale::where('menu_id', $menu->id)->exists();

        if ($hasData) {
            return back()->with('error',
                'Menu "' . $menu->name . '" tidak bisa dihapus karena sudah ada data penjualan. '
                . 'Nonaktifkan menu jika tidak ingin digunakan lagi.');
        }

        // Hapus resep dulu (cascade) â€” resep tanpa transaksi aman dihapus
        $menu->recipes()->delete();
        $menu->delete();
        return back()->with('success', 'Menu dihapus.');
    }

    public function destroyRecipeVersion(Menu $menu, string $group)
    {
        $query = $group === 'kosong'
            ? $menu->recipes()->whereNull('recipe_group_id')
            : $menu->recipes()->where('recipe_group_id', $group);
        $query->delete();
        return back()->with('success', 'Versi resep dihapus.');
    }
}
