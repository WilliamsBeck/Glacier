<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\IngredientPackaging;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class IngredientSeeder extends Seeder
{
    public function run(): void
    {
        $zhi    = Supplier::where('type', 'zhisheng')->first()?->id;
        $local1 = Supplier::where('name', 'CV Sumber Rasa')->first()?->id;
        $local2 = Supplier::where('name', 'PT Manis Sejahtera')->first()?->id;
        $other  = Supplier::where('type', 'other')->first()?->id;

        // [name, category, type, unit_base, [packagings: [name, crate_to_pack, pack_to_base, supplier_id]]]
        // Sebagian besar bahan dual-source: Zhisheng (pusat) + supplier lokal sebagai backup
        $data = [
            // SOLID
            ['Susu Cair UHT',     'solid', 'raw', 'ml', [
                ['Karton 12 x 1L', 12, 1000, $zhi],
                ['Karton 24 x 250ml', 24, 250, $local1],
            ]],
            ['Gula Pasir',        'solid', 'raw', 'gram', [
                ['Karung 25kg', 1, 25000, $zhi],
                ['Karung 50kg', 1, 50000, $local1],
            ]],
            ['Garam',             'solid', 'raw', 'gram', [
                ['Pack 1kg', 12, 1000, $local1],
            ]],
            ['Krimer Bubuk',      'solid', 'raw', 'gram', [
                ['Sak 25kg', 1, 25000, $zhi],
            ]],

            // BUBUK
            ['Tepung Es Krim',    'bubuk', 'raw', 'gram', [
                ['Sak 10kg', 1, 10000, $zhi],
            ]],
            ['Bubuk Coklat',      'bubuk', 'raw', 'gram', [
                ['Pack 1kg', 10, 1000, $zhi],
            ]],
            ['Bubuk Matcha',      'bubuk', 'raw', 'gram', [
                ['Pack 500g', 20, 500, $zhi],
            ]],
            ['Bubuk Taro',        'bubuk', 'raw', 'gram', [
                ['Pack 1kg', 10, 1000, $zhi],
            ]],

            // TEH
            ['Teh Hitam',         'teh', 'raw', 'gram', [
                ['Pack 500g', 20, 500, $zhi],
            ]],
            ['Teh Hijau',         'teh', 'raw', 'gram', [
                ['Pack 500g', 20, 500, $zhi],
            ]],
            ['Teh Oolong',        'teh', 'raw', 'gram', [
                ['Pack 500g', 20, 500, $zhi],
            ]],

            // SIRUP
            ['Sirup Lemon',       'sirup', 'raw', 'ml', [
                ['Botol 1L', 12, 1000, $zhi],
                ['Botol 750ml', 12, 750, $local2],
            ]],
            ['Sirup Strawberry',  'sirup', 'raw', 'ml', [
                ['Botol 1L', 12, 1000, $zhi],
                ['Botol 750ml', 12, 750, $local2],
            ]],
            ['Sirup Vanilla',     'sirup', 'raw', 'ml', [
                ['Botol 1L', 12, 1000, $zhi],
            ]],
            ['Sirup Caramel',     'sirup', 'raw', 'ml', [
                ['Botol 1L', 12, 1000, $zhi],
            ]],

            // SELAI
            ['Selai Blueberry',   'selai', 'raw', 'gram', [
                ['Jar 1kg', 6, 1000, $zhi],
                ['Jar 500g', 12, 500, $local2],
            ]],
            ['Selai Mangga',      'selai', 'raw', 'gram', [
                ['Jar 1kg', 6, 1000, $zhi],
            ]],
            ['Selai Coklat',      'selai', 'raw', 'gram', [
                ['Jar 1kg', 6, 1000, $zhi],
            ]],

            // KEMASAN
            ['Cup 16oz',          'kemasan', 'raw', 'pcs', [
                ['Pack 50pcs', 20, 50, $zhi],
                ['Pack 50pcs (lokal)', 20, 50, $local1],
            ]],
            ['Cup 22oz',          'kemasan', 'raw', 'pcs', [
                ['Pack 50pcs', 20, 50, $zhi],
            ]],
            ['Tutup Cup 16oz',    'kemasan', 'raw', 'pcs', [
                ['Pack 50pcs', 20, 50, $zhi],
            ]],
            ['Tutup Cup 22oz',    'kemasan', 'raw', 'pcs', [
                ['Pack 50pcs', 20, 50, $zhi],
            ]],
            ['Sedotan Jumbo',     'kemasan', 'raw', 'pcs', [
                ['Pack 100pcs', 50, 100, $zhi],
                ['Pack 100pcs (lokal)', 50, 100, $local1],
            ]],
            ['Cone Es Krim',      'kemasan', 'raw', 'pcs', [
                ['Pack 100pcs', 10, 100, $zhi],
            ]],
            ['Paper Bag',         'kemasan', 'raw', 'pcs', [
                ['Pack 100pcs', 10, 100, $other],
            ]],

            // SEMI-FINISHED
            ['Base Es Krim Vanilla', 'solid', 'semi_finished', 'gram', []],
            ['Base Es Krim Coklat',  'solid', 'semi_finished', 'gram', []],
            ['Base Teh Manis',       'teh',   'semi_finished', 'ml',   []],
        ];

        foreach ($data as $row) {
            [$name, $cat, $type, $unit, $packagings] = $row;
            $ing = Ingredient::updateOrCreate(
                ['name' => $name],
                ['category' => $cat, 'type' => $type, 'unit_base' => $unit, 'is_active' => true]
            );

            foreach ($packagings as $p) {
                [$pname, $crate, $base, $sup] = $p;
                IngredientPackaging::updateOrCreate(
                    ['ingredient_id' => $ing->id, 'packaging_name' => $pname],
                    [
                        'supplier_id'   => $sup,
                        'crate_to_pack' => $crate,
                        'pack_to_base'  => $base,
                        'is_active'     => true,
                    ]
                );
            }
        }

        $this->command->info('Ingredients seeded: ' . Ingredient::count());
    }
}
