<?php
namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\{IngredientCategory, Ingredient};
use Illuminate\Http\Request;

class IngredientCategoryController extends Controller
{
    public function index()
    {
        $categories = IngredientCategory::ordered()->get();
        return view('master.ingredient-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:50',
        ]);

        // Auto-generate slug dari label: huruf kecil, spasi → underscore
        $name = strtolower(trim(preg_replace('/\s+/', '_', $request->label)));

        if (IngredientCategory::where('name', $name)->exists()) {
            return back()->withErrors(['label' => 'Kategori "' . $request->label . '" sudah ada.'])->withInput();
        }

        $maxOrder = IngredientCategory::max('sort_order') ?? 0;
        IngredientCategory::create([
            'name'       => $name,
            'label'      => $request->label,
            'sort_order' => $maxOrder + 1,
        ]);

        return back()->with('success', 'Kategori "' . $request->label . '" berhasil ditambahkan.');
    }

    public function update(Request $request, IngredientCategory $ingredientCategory)
    {
        $request->validate(['label' => 'required|string|max:50']);
        $ingredientCategory->update(['label' => $request->label]);
        return back()->with('success', 'Label kategori diperbarui.');
    }

    public function destroy(IngredientCategory $ingredientCategory)
    {
        $inUse = Ingredient::where('category', $ingredientCategory->name)->exists();
        if ($inUse) {
            return back()->withErrors(['error' => 'Kategori ini masih digunakan oleh bahan baku. Ubah kategori bahan tersebut terlebih dahulu.']);
        }
        $ingredientCategory->delete();
        return back()->with('success', 'Kategori dihapus.');
    }

    public function reorder(Request $request)
    {
        $request->validate(['order' => 'required|array']);
        foreach ($request->order as $sortOrder => $id) {
            IngredientCategory::where('id', $id)->update(['sort_order' => $sortOrder + 1]);
        }
        return response()->json(['ok' => true]);
    }
}
