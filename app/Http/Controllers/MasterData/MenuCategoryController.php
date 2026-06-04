<?php
namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\{MenuCategory, Menu};
use Illuminate\Http\Request;

class MenuCategoryController extends Controller
{
    public function index()
    {
        $categories = MenuCategory::ordered()->get();
        return view('master.menu-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:50|unique:menu_categories,name'],
            ['name.unique' => 'Kategori ini sudah ada.']);

        $maxOrder = MenuCategory::max('sort_order') ?? 0;
        MenuCategory::create(['name' => $request->name, 'sort_order' => $maxOrder + 1]);

        return $this->backToMenuTab()->with('success', 'Kategori "' . $request->name . '" ditambahkan.');
    }

    public function update(Request $request, MenuCategory $menuCategory)
    {
        $request->validate(['name' => 'required|string|max:50']);
        $menuCategory->update(['name' => $request->name]);
        return $this->backToMenuTab()->with('success', 'Kategori diperbarui.');
    }

    public function destroy(MenuCategory $menuCategory)
    {
        $inUse = Menu::where('category_id', $menuCategory->id)->exists();
        if ($inUse) {
            return $this->backToMenuTab()->withErrors(['error' => 'Kategori masih digunakan oleh menu. Ubah kategori menu tersebut dulu.']);
        }
        $menuCategory->delete();
        return $this->backToMenuTab()->with('success', 'Kategori dihapus.');
    }

    private function backToMenuTab()
    {
        return redirect()->to(route('master.categories.index') . '#menu');
    }

    public function reorder(Request $request)
    {
        foreach ($request->order as $sortOrder => $id) {
            MenuCategory::where('id', $id)->update(['sort_order' => $sortOrder + 1]);
        }
        return response()->json(['ok' => true]);
    }
}
