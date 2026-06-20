<?php
namespace App\Http\Controllers;
use App\Models\{Store, Ingredient, StoreStock, MutationItem, StockLedger, IngredientPackaging};
use App\Services\StockLedgerService;
use Illuminate\Http\Request;

class ForecastingController extends Controller
{
    public function index()
    {
        $stores      = auth()->user()->accessibleStores();
        $ingredients = Ingredient::where('ingredients.is_active', true)->where('type', 'raw')->orderedByCategory()->get();
        return view('forecasting.index', compact('stores', 'ingredients'));
    }

    public function calculate(Request $request)
    {
        $request->validate([
            'store_id'    => 'required|exists:stores,id',
            'days_needed' => 'required|integer|min:1|max:90',
            'buffer_pct'  => 'required|integer|min:0|max:100',
            'ref_days'    => 'required|integer|min:7|max:90',
        ]);

        $storeId   = $request->store_id;
        $days      = $request->days_needed;
        $bufferPct = $request->buffer_pct;
        $refDays   = $request->ref_days;
        $dateFrom  = now()->subDays($refDays)->format('Y-m-d');

        $usages = StockLedger::where('store_id', $storeId)
            ->whereIn('movement_type', ['production_out', 'sale_deduction', 'waste'])
            ->where('movement_date', '>=', $dateFrom)
            ->selectRaw('ingredient_id, ABS(SUM(qty_change)) as total_used')
            ->groupBy('ingredient_id')
            ->get();

        $results = [];
        foreach ($usages as $usage) {
            $ingredient = Ingredient::find($usage->ingredient_id);
            if (!$ingredient) continue;

            $dailyAvg   = $usage->total_used / $refDays;
            $needed     = $dailyAvg * $days * (1 + $bufferPct / 100);
            $currentQty = StoreStock::where('store_id', $storeId)
                ->where('ingredient_id', $usage->ingredient_id)
                ->value('stock_balance') ?? 0;
            $toBuyBase  = max(0, $needed - $currentQty);
            $lastPrice  = MutationItem::whereHas('mutation', fn($q) =>
                $q->where('destination_store_id', $storeId)->where('status', 'confirmed')
            )->where('ingredient_id', $usage->ingredient_id)->latest()->value('price_per_base') ?? 0;

            if ($toBuyBase > 0) {
                $packaging = IngredientPackaging::where('ingredient_id', $ingredient->id)
                    ->where('is_active', true)
                    ->first();

                $dus  = 0;
                $pack = 0;
                if ($packaging && $toBuyBase > 0) {
                    $totalPacks = $toBuyBase / max($packaging->pack_to_base, 0.0001);
                    $dus  = (int) floor($totalPacks / max($packaging->crate_to_pack, 1));
                    $pack = (int) ceil(fmod($totalPacks, max($packaging->crate_to_pack, 1)));
                }

                $results[] = [
                    'ingredient'  => $ingredient,
                    'avg_per_day' => round($dailyAvg, 2),
                    'needed'      => round($needed, 2),
                    'current_qty' => round($currentQty, 2),
                    'to_buy_base' => round($toBuyBase, 2),
                    'packaging'   => $packaging,
                    'dus'         => $dus,
                    'pack'        => $pack,
                    'last_price'  => $lastPrice,
                    'est_budget'  => round($toBuyBase * $lastPrice, 0),
                ];
            }
        }

        $stores      = auth()->user()->accessibleStores();
        $ingredients = Ingredient::where('ingredients.is_active', true)->where('type', 'raw')->orderedByCategory()->get();
        return view('forecasting.index', compact('results', 'stores', 'ingredients'));
    }
}
