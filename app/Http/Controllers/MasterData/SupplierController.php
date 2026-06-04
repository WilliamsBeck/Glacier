<?php
namespace App\Http\Controllers\MasterData;
use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();
        if ($request->search) $query->where('name','like',"%{$request->search}%");
        if ($request->type)   $query->where('type',$request->type);
        $suppliers = $query->latest()->paginate(20);
        return view('master.suppliers.index', compact('suppliers'));
    }
    public function create() { return view('master.suppliers.form'); }
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'=>'required|string',
            'type'=>'required|in:zhisheng,local_supplier,other',
            'contact'=>'nullable|string','address'=>'nullable|string',
        ]);
        $data['is_active'] = $request->has('is_active');
        Supplier::create($data);
        return redirect()->route('master.suppliers.index')->with('success','Supplier ditambahkan.');
    }
    public function edit(Supplier $supplier) { return view('master.suppliers.form', compact('supplier')); }
    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name'=>'required|string',
            'type'=>'required|in:zhisheng,local_supplier,other',
            'contact'=>'nullable|string','address'=>'nullable|string',
        ]);
        $data['is_active'] = $request->has('is_active');
        $supplier->update($data);
        return redirect()->route('master.suppliers.index')->with('success','Supplier diupdate.');
    }
    public function destroy(Supplier $supplier)
    {
        $hasData = \App\Models\Mutation::where('supplier_id', $supplier->id)->exists()
                || \App\Models\IngredientPackaging::where('supplier_id', $supplier->id)->exists();

        if ($hasData) {
            return back()->with('error',
                'Supplier "' . $supplier->name . '" tidak bisa dihapus karena sudah dipakai di mutasi/kemasan. '
                . 'Nonaktifkan supplier jika tidak ingin digunakan lagi.');
        }

        $supplier->delete();
        return back()->with('success','Supplier dihapus.');
    }
}
