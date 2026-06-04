<?php
namespace App\Http\Controllers\Inventory;
use App\Http\Controllers\Controller;
use App\Models\{StockLedger, Store, Ingredient};
use Illuminate\Http\Request;

class StockLedgerController extends Controller
{
    public function index(Request $request)
    {
        $storeIds = auth()->user()->accessibleStoreIds();
        $query    = StockLedger::with(['store','ingredient','createdBy'])
            ->whereIn('store_id',$storeIds);
        if ($request->store_id)      $query->where('store_id',$request->store_id);
        if ($request->ingredient_id) $query->where('ingredient_id',$request->ingredient_id);
        if ($request->date_from)     $query->where('movement_date','>=',$request->date_from);
        if ($request->date_to)       $query->where('movement_date','<=',$request->date_to);
        if ($request->movement_type) $query->where('movement_type',$request->movement_type);
        $ledgers     = $query->latest('created_at')->paginate(30);
        $stores      = auth()->user()->accessibleStores();
        $ingredients = Ingredient::where('is_active',true)->orderBy('name')->get();
        return view('inventory.ledger.index', compact('ledgers','stores','ingredients'));
    }
}
