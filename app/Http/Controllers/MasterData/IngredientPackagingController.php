<?php
namespace App\Http\Controllers\MasterData;
use App\Http\Controllers\Controller;
use App\Models\{IngredientPackaging, Ingredient, Supplier};
use Illuminate\Http\Request;

class IngredientPackagingController extends Controller
{
    public function create(Request $request)
    {
        $ingredients = Ingredient::where('ingredients.is_active',true)->orderedByCategory()->get();
        $suppliers   = Supplier::where('is_active',true)->orderBy('name')->get();
        $selectedIngredient = $request->ingredient_id;
        return view('master.packagings.form', compact('ingredients','suppliers','selectedIngredient'));
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'ingredient_id'=>'required|exists:ingredients,id',
            'supplier_id'=>'nullable|exists:suppliers,id',
            'packaging_name'=>'required|string',
            'crate_to_pack'=>'required|integer|min:1',
            'pack_to_base'=>'required|numeric|min:0.0001',
        ]);
        $data['is_active'] = $request->has('is_active');
        IngredientPackaging::create($data);
        return redirect()->route('master.ingredients.edit', $data['ingredient_id'])
            ->with('success','Kemasan ditambahkan.');
    }
    public function edit(IngredientPackaging $packaging)
    {
        $ingredients = Ingredient::where('ingredients.is_active',true)->orderedByCategory()->get();
        $suppliers   = Supplier::where('is_active',true)->orderBy('name')->get();
        return view('master.packagings.form', compact('packaging','ingredients','suppliers'));
    }
    public function update(Request $request, IngredientPackaging $packaging)
    {
        $data = $request->validate([
            'ingredient_id'=>'required|exists:ingredients,id',
            'supplier_id'=>'nullable|exists:suppliers,id',
            'packaging_name'=>'required|string',
            'crate_to_pack'=>'required|integer|min:1',
            'pack_to_base'=>'required|numeric|min:0.0001',
        ]);
        $data['is_active'] = $request->has('is_active');
        $packaging->update($data);
        return redirect()->route('master.ingredients.edit', $data['ingredient_id'])
            ->with('success','Kemasan diupdate.');
    }
    public function destroy(IngredientPackaging $packaging)
    {
        $ingredientId = $packaging->ingredient_id;

        $hasData = \App\Models\MutationItem::where('packaging_id', $packaging->id)->exists();
        if ($hasData) {
            return back()->with('error',
                'Kemasan "' . $packaging->packaging_name . '" tidak bisa dihapus karena sudah dipakai di mutasi. '
                . 'Nonaktifkan kemasan jika tidak ingin digunakan lagi.');
        }

        $packaging->delete();
        return redirect()->route('master.ingredients.edit', $ingredientId)->with('success','Kemasan dihapus.');
    }

    /**
     * Toggle aktif/nonaktif kemasan via AJAX.
     * Kemasan nonaktif tidak tampil di Saldo Stok & tidak bisa dipilih di form mutasi.
     */
    public function toggleActive(IngredientPackaging $packaging)
    {
        $packaging->is_active = !$packaging->is_active;
        $packaging->save();

        return response()->json([
            'ok'        => true,
            'is_active' => $packaging->is_active,
            'message'   => $packaging->is_active
                ? 'Kemasan "' . $packaging->packaging_name . '" diaktifkan.'
                : 'Kemasan "' . $packaging->packaging_name . '" dinonaktifkan.',
        ]);
    }
}
