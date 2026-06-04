<?php
namespace App\Http\Controllers\MasterData;
use App\Http\Controllers\Controller;
use App\Models\{Ingredient, IngredientCategory, IngredientComposition, IngredientPackaging, Supplier};
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    public function index(Request $request)
    {
        $query = Ingredient::query();
        if ($request->search) $query->where('name','like',"%{$request->search}%");
        if ($request->type)   $query->where('type',$request->type);
        $ingredients = $query->latest()->paginate(20);
        return view('master.ingredients.index', compact('ingredients'));
    }

    public function create()
    {
        $suppliers      = Supplier::where('is_active', true)->orderBy('name')->get();
        $rawIngredients = Ingredient::where('is_active', true)->where('type', 'raw')->orderBy('name')->get(['id', 'name', 'unit_base']);
        $categories     = IngredientCategory::ordered()->get();
        return view('master.ingredients.form', compact('suppliers', 'rawIngredients', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string',
            'type'      => 'required|in:raw,semi_finished',
            'category'  => 'nullable|in:' . implode(',', IngredientCategory::orderedNames()),
            'unit_base' => 'required|in:gram,pcs',
        ]);
        $data['is_active'] = $request->has('is_active');
        $data['category']  = $request->type === 'raw' ? $request->category : null;

        $ingredient = Ingredient::create($data);

        if ($request->type === 'raw') {
            foreach ($request->input('packagings', []) as $pack) {
                if (empty($pack['packaging_name']) || empty($pack['crate_to_pack'])) continue;
                $ingredient->packagings()->create([
                    'supplier_id'    => $pack['supplier_id'] ?: null,
                    'packaging_name' => $pack['packaging_name'],
                    'crate_to_pack'  => $pack['crate_to_pack'],
                    'pack_to_base'   => $pack['pack_to_base'],
                    'is_active'      => true,
                ]);
            }
        }

        if ($request->input('action') === 'save_and_new') {
            return redirect()->route('master.ingredients.create')
                ->with('success', "Bahan \"{$ingredient->name}\" ditambahkan. Silakan input bahan berikutnya.");
        }

        return redirect()->route('master.ingredients.index')->with('success', 'Bahan ditambahkan.');
    }

    public function show(Ingredient $ingredient)
    {
        $ingredient->load(['packagings.supplier', 'compositions.child']);
        $suppliers      = Supplier::where('is_active', true)->orderBy('name')->get();
        $rawIngredients = Ingredient::where('is_active', true)->where('type', 'raw')->orderBy('name')->get(['id', 'name', 'unit_base']);
        $categories     = IngredientCategory::ordered()->get();
        return view('master.ingredients.form', compact('ingredient', 'suppliers', 'rawIngredients', 'categories'));
    }

    public function edit(Ingredient $ingredient)
    {
        $ingredient->load(['packagings.supplier', 'compositions.child']);
        $suppliers      = Supplier::where('is_active', true)->orderBy('name')->get();
        $rawIngredients = Ingredient::where('is_active', true)->where('type', 'raw')->orderBy('name')->get(['id', 'name', 'unit_base']);
        $categories     = IngredientCategory::ordered()->get();
        return view('master.ingredients.form', compact('ingredient', 'suppliers', 'rawIngredients', 'categories'));
    }

    public function update(Request $request, Ingredient $ingredient)
    {
        $data = $request->validate([
            'name'      => 'required|string',
            'type'      => 'required|in:raw,semi_finished',
            'category'  => 'nullable|in:' . implode(',', IngredientCategory::orderedNames()),
            'unit_base' => 'required|in:gram,pcs',
        ]);
        $data['is_active'] = $request->has('is_active');
        $data['category']  = $request->type === 'raw' ? $request->category : null;
        $ingredient->update($data);

        if ($request->type === 'raw') {
            // Update kemasan yang sudah ada
            foreach ($request->input('existing_packagings', []) as $packId => $pack) {
                if (empty($pack['packaging_name']) || empty($pack['crate_to_pack'])) continue;
                $ingredient->packagings()->where('id', $packId)->update([
                    'supplier_id'    => $pack['supplier_id'] ?: null,
                    'packaging_name' => $pack['packaging_name'],
                    'crate_to_pack'  => $pack['crate_to_pack'],
                    'pack_to_base'   => $pack['pack_to_base'],
                ]);
            }
            // Tambah kemasan baru
            foreach ($request->input('packagings', []) as $pack) {
                if (empty($pack['packaging_name']) || empty($pack['crate_to_pack'])) continue;
                $ingredient->packagings()->create([
                    'supplier_id'    => $pack['supplier_id'] ?: null,
                    'packaging_name' => $pack['packaging_name'],
                    'crate_to_pack'  => $pack['crate_to_pack'],
                    'pack_to_base'   => $pack['pack_to_base'],
                    'is_active'      => true,
                ]);
            }
        }

        $totalOutput = (float) $request->input('total_output', 0);
        if ($totalOutput > 0) {
            foreach ($request->input('compositions', []) as $comp) {
                if (empty($comp['child_id']) || empty($comp['qty_used'])) continue;
                $qtyRaw    = (float) $comp['qty_used'];
                $qtyNeeded = $qtyRaw / $totalOutput;  // disimpan sebagai fallback
                IngredientComposition::updateOrCreate(
                    ['parent_id' => $ingredient->id, 'child_id' => $comp['child_id']],
                    [
                        'qty_needed' => $qtyNeeded,   // fallback decimal
                        'qty_raw'    => $qtyRaw,      // pembilang asli (misal: 3000)
                        'qty_output' => $totalOutput, // penyebut asli (misal: 11000)
                    ]
                );
            }
        }

        // Hapus komposisi yang di-request untuk dihapus
        foreach ($request->input('delete_compositions', []) as $compId) {
            IngredientComposition::where('id', $compId)->where('parent_id', $ingredient->id)->delete();
        }

        return redirect()->route('master.ingredients.edit', $ingredient)->with('success', 'Bahan diupdate.');
    }

    public function destroy(Ingredient $ingredient)
    {
        $hasData = \App\Models\MutationItem::where('ingredient_id', $ingredient->id)->exists()
                || \App\Models\Recipe::where('ingredient_id', $ingredient->id)->exists()
                || \App\Models\DailyUsage::where('ingredient_id', $ingredient->id)->exists()
                || \App\Models\OpnameItem::where('ingredient_id', $ingredient->id)->exists()
                || \App\Models\StockLedger::where('ingredient_id', $ingredient->id)->exists()
                || \App\Models\StoreStock::where('ingredient_id', $ingredient->id)
                    ->where('stock_balance', '>', 0)->exists()
                || IngredientComposition::where('child_id', $ingredient->id)
                    ->orWhere('parent_id', $ingredient->id)->exists()
                || \App\Models\WasteLogItem::where('ingredient_id', $ingredient->id)->exists()
                || \App\Models\ProductionLogItem::where('ingredient_id', $ingredient->id)->exists();

        if ($hasData) {
            return back()->with('error',
                'Bahan "' . $ingredient->name . '" tidak bisa dihapus karena sudah dipakai di transaksi/resep. '
                . 'Nonaktifkan saja kalau tidak ingin digunakan lagi.');
        }

        // Hapus kemasan dulu (cascade-ish)
        $ingredient->packagings()->delete();
        $ingredient->delete();
        return back()->with('success', 'Bahan dihapus.');
    }

    public function packagings(Ingredient $ingredient)
    {
        return response()->json($ingredient->packagings()->where('is_active', true)->get());
    }

    public function compositions(Ingredient $ingredient)
    {
        return response()->json($ingredient->compositions()->with('child')->get());
    }
}
