<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\Mutation;
use App\Models\Store;
use App\Models\User;
use App\Services\MutationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class StoreStockSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'super_admin')->first();
        Auth::loginUsingId($admin->id);

        $date = now()->startOfMonth()->subMonth()->toDateString();

        // [ingredient_name, qty_base_per_store, price_per_base]
        $opening = [
            ['Susu Cair UHT',    60000, 12],
            ['Gula Pasir',       50000, 14],
            ['Garam',             5000,  3],
            ['Krimer Bubuk',     15000, 60],
            ['Tepung Es Krim',   20000, 80],
            ['Bubuk Coklat',      8000, 120],
            ['Bubuk Matcha',      4000, 350],
            ['Bubuk Taro',        5000, 100],
            ['Teh Hitam',         5000, 60],
            ['Teh Hijau',         5000, 70],
            ['Teh Oolong',        4000, 90],
            ['Sirup Lemon',      12000, 35],
            ['Sirup Strawberry', 12000, 35],
            ['Sirup Vanilla',     8000, 40],
            ['Sirup Caramel',     8000, 40],
            ['Selai Blueberry',   6000, 55],
            ['Selai Mangga',      6000, 50],
            ['Selai Coklat',      6000, 52],
            ['Cup 16oz',          2000, 600],
            ['Cup 22oz',          1500, 800],
            ['Tutup Cup 16oz',    2000, 200],
            ['Tutup Cup 22oz',    1500, 250],
            ['Sedotan Jumbo',     5000, 100],
            ['Cone Es Krim',      1000, 500],
            ['Paper Bag',          500, 350],
        ];

        $ingredients = Ingredient::with('packagings')->get()->keyBy('name');

        foreach (Store::all() as $store) {
            // Skip kalau toko ini sudah punya opening_stock
            $exists = Mutation::where('destination_store_id', $store->id)
                ->where('type', 'opening_stock')->exists();
            if ($exists) continue;

            $mutation = Mutation::create([
                'type'                 => 'opening_stock',
                'destination_store_id' => $store->id,
                'transaction_date'     => $date,
                'delivery_date'        => $date,
                'status'               => 'draft',
                'notes'                => 'Opening stock from seeder',
                'created_by'           => $admin->id,
            ]);

            foreach ($opening as [$name, $qty, $price]) {
                $ing = $ingredients[$name] ?? null;
                if (! $ing) continue;
                $pkg = $ing->packagings->first();

                $mutation->items()->create([
                    'ingredient_id'  => $ing->id,
                    'packaging_id'   => $pkg?->id,
                    'qty_base'       => $qty,
                    'total_in_base'  => $qty,
                    'price_per_base' => $price,
                    'cost_subtotal'  => $qty * $price,
                    'remaining_qty'  => $qty,
                ]);
            }

            MutationService::confirm($mutation);
        }

        Auth::logout();
        $this->command->info('Opening stock mutations seeded.');
    }
}
