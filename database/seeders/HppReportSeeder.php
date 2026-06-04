<?php

namespace Database\Seeders;

use App\Models\{HppMonthlyReport, Menu, MonthlySale, MutationItem, Recipe, Store};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HppReportSeeder extends Seeder
{
    public function run(): void
    {
        $now    = now();
        $stores = Store::all();

        foreach ($stores as $store) {
            for ($i = 0; $i < 3; $i++) {
                $d        = $now->copy()->subMonthsNoOverflow($i);
                $month    = (int) $d->format('m');
                $year     = (int) $d->format('Y');
                $dateFrom = $d->copy()->startOfMonth()->toDateString();
                $dateTo   = $d->copy()->endOfMonth()->toDateString();

                $this->seedForStorePeriod($store->id, $month, $year, $dateFrom, $dateTo);
            }
        }

        $this->command->info('HPP monthly reports seeded: ' . HppMonthlyReport::count());
    }

    private function seedForStorePeriod(int $storeId, int $month, int $year, string $from, string $to): void
    {
        // 1. Ideal usage per ingredient = Σ(total_sold × recipe.qty_usage) untuk semua menu
        $sales = MonthlySale::where('store_id', $storeId)
            ->where('month', $month)->where('year', $year)
            ->get()
            ->groupBy('menu_id')
            ->map(fn ($g) => $g->sum('total_sold'));

        $idealUsage = [];

        foreach ($sales as $menuId => $totalSold) {
            $recipes = Recipe::where('menu_id', $menuId)
                ->where(fn ($q) => $q->whereNull('store_id')->orWhere('store_id', $storeId))
                ->where('effective_from', '<=', $to)
                ->orderByDesc('effective_from')
                ->get()
                ->unique('ingredient_id'); // versi terbaru per bahan

            foreach ($recipes as $r) {
                $idealUsage[$r->ingredient_id] = ($idealUsage[$r->ingredient_id] ?? 0)
                    + ($totalSold * (float) $r->qty_usage);
            }
        }

        if (empty($idealUsage)) return;

        // 2a. Harga rata-rata per ingredient untuk RAW (dari mutation_items)
        $avgPrices = MutationItem::query()
            ->join('mutations', 'mutations.id', '=', 'mutation_items.mutation_id')
            ->where('mutations.destination_store_id', $storeId)
            ->where('mutations.status', 'confirmed')
            ->whereIn('mutation_items.ingredient_id', array_keys($idealUsage))
            ->select('mutation_items.ingredient_id',
                DB::raw('SUM(total_in_base * price_per_base) / SUM(total_in_base) as avg_price'))
            ->groupBy('mutation_items.ingredient_id')
            ->pluck('avg_price', 'ingredient_id')
            ->toArray();

        // 2b. Harga semi-finished dari production_log_items (cost bahan / qty produced)
        $logCosts = DB::table('production_logs')
            ->join('production_log_items', 'production_logs.id', '=', 'production_log_items.production_log_id')
            ->where('production_logs.store_id', $storeId)
            ->whereIn('production_logs.semi_finished_id', array_keys($idealUsage))
            ->select('production_logs.id', 'production_logs.semi_finished_id',
                'production_logs.qty_produced',
                DB::raw('SUM(production_log_items.qty_consumed * production_log_items.price_per_base) as total_cost'))
            ->groupBy('production_logs.id', 'production_logs.semi_finished_id', 'production_logs.qty_produced')
            ->get();

        $semiPrices = $logCosts->groupBy('semi_finished_id')->map(function ($logs) {
            $sumCost = $logs->sum('total_cost');
            $sumQty  = $logs->sum('qty_produced');
            return $sumQty > 0 ? $sumCost / $sumQty : 0;
        })->toArray();

        $avgPrices = $avgPrices + $semiPrices;

        foreach ($idealUsage as $ingredientId => $ideal) {
            $avgPrice = (float) ($avgPrices[$ingredientId] ?? 0);

            // Actual = ideal × overusage 0–15% (variance realistis)
            $multiplier   = 1 + (random_int(0, 1500) / 10000);  // 1.0000 – 1.1500
            $actual       = round($ideal * $multiplier, 4);
            $varianceQty  = $actual - $ideal;
            $hppIdeal     = $ideal  * $avgPrice;
            $hppActual    = $actual * $avgPrice;
            $varianceAmt  = $hppActual - $hppIdeal;

            HppMonthlyReport::updateOrCreate(
                [
                    'store_id'      => $storeId,
                    'ingredient_id' => $ingredientId,
                    'month'         => $month,
                    'year'          => $year,
                    'period_type'   => 'end_month',
                ],
                [
                    'date_from'          => $from,
                    'date_to'            => $to,
                    'ideal_usage_base'   => $ideal,
                    'actual_usage_base'  => $actual,
                    'avg_price_per_base' => $avgPrice,
                    'hpp_ideal'          => $hppIdeal,
                    'hpp_actual'         => $hppActual,
                    'variance_qty'       => $varianceQty,
                    'variance_amount'    => $varianceAmt,
                ]
            );
        }
    }
}
