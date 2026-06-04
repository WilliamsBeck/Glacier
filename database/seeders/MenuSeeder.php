<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $cat = fn (string $n) => MenuCategory::where('name', $n)->first()?->id;
        $ing = fn (string $n) => Ingredient::where('name', $n)->first();

        $createdBy = User::where('role', 'super_admin')->first()?->id ?? 1;

        // [menu_name, category, [ [ingredient_name, qty, unit], ... ]]
        $menus = [
            ['Ice Cream Cone', 'Ice Cream', [
                ['Base Es Krim Vanilla', 80,  'gram'],
                ['Cone Es Krim', 1, 'pcs'],
            ]],
            ['Choco Sundae', 'Ice Cream', [
                ['Base Es Krim Coklat', 80, 'gram'],
                ['Cup 16oz', 1, 'pcs'],
            ]],
            ['Matcha Latte', 'Milk Tea', [
                ['Bubuk Matcha', 15, 'gram'],
                ['Susu Cair UHT', 200, 'ml'],
                ['Gula Pasir', 15, 'gram'],
                ['Cup 22oz', 1, 'pcs'],
                ['Tutup Cup 22oz', 1, 'pcs'],
                ['Sedotan Jumbo', 1, 'pcs'],
            ]],
            ['Taro Milk Tea', 'Milk Tea', [
                ['Bubuk Taro', 20, 'gram'],
                ['Susu Cair UHT', 180, 'ml'],
                ['Gula Pasir', 15, 'gram'],
                ['Cup 22oz', 1, 'pcs'],
                ['Tutup Cup 22oz', 1, 'pcs'],
                ['Sedotan Jumbo', 1, 'pcs'],
            ]],
            ['Caramel Coffee', 'Coffee', [
                ['Sirup Caramel', 25, 'ml'],
                ['Susu Cair UHT', 200, 'ml'],
                ['Cup 16oz', 1, 'pcs'],
                ['Tutup Cup 16oz', 1, 'pcs'],
                ['Sedotan Jumbo', 1, 'pcs'],
            ]],
            ['Teh Oolong Classic', 'Fruit Tea', [
                ['Teh Oolong', 6, 'gram'],
                ['Gula Pasir', 18, 'gram'],
                ['Cup 16oz', 1, 'pcs'],
                ['Tutup Cup 16oz', 1, 'pcs'],
                ['Sedotan Jumbo', 1, 'pcs'],
            ]],
            ['Milk Tea Original', 'Milk Tea', [
                ['Base Teh Manis', 300, 'ml'],
                ['Susu Cair UHT',  100, 'ml'],
                ['Cup 16oz', 1, 'pcs'],
                ['Tutup Cup 16oz', 1, 'pcs'],
                ['Sedotan Jumbo', 1, 'pcs'],
            ]],
            ['Fruit Tea Lemon', 'Fruit Tea', [
                ['Teh Hijau', 5, 'gram'],
                ['Sirup Lemon', 30, 'ml'],
                ['Gula Pasir', 20, 'gram'],
                ['Cup 16oz', 1, 'pcs'],
                ['Tutup Cup 16oz', 1, 'pcs'],
                ['Sedotan Jumbo', 1, 'pcs'],
            ]],
            ['Fruit Tea Strawberry', 'Fruit Tea', [
                ['Teh Hijau', 5, 'gram'],
                ['Sirup Strawberry', 30, 'ml'],
                ['Gula Pasir', 20, 'gram'],
                ['Cup 16oz', 1, 'pcs'],
                ['Tutup Cup 16oz', 1, 'pcs'],
                ['Sedotan Jumbo', 1, 'pcs'],
            ]],
            ['Blueberry Sundae', 'Ice Cream', [
                ['Base Es Krim Vanilla', 80, 'gram'],
                ['Selai Blueberry', 25, 'gram'],
                ['Cup 16oz', 1, 'pcs'],
            ]],
            ['Mango Sundae', 'Ice Cream', [
                ['Base Es Krim Vanilla', 80, 'gram'],
                ['Selai Mangga', 25, 'gram'],
                ['Cup 16oz', 1, 'pcs'],
            ]],
        ];

        foreach ($menus as [$name, $catName, $items]) {
            $menu = Menu::updateOrCreate(['name' => $name], [
                'category'    => $catName,
                'category_id' => $cat($catName),
                'is_active'   => true,
            ]);

            // Recipe global (store_id null) — 1 grup uuid untuk seluruh items
            if ($menu->recipes()->whereNull('store_id')->exists()) continue;

            $groupId = (string) Str::uuid();
            foreach ($items as [$iname, $qty, $unit]) {
                $i = $ing($iname);
                if (! $i) continue;
                Recipe::create([
                    'menu_id'         => $menu->id,
                    'store_id'        => null,
                    'recipe_group_id' => $groupId,
                    'ingredient_id'   => $i->id,
                    'qty_usage'       => $qty,
                    'unit'            => $unit,
                    'effective_from'  => now()->startOfYear()->toDateString(),
                    'created_by'      => $createdBy,
                ]);
            }
        }

        $this->command->info('Menus + recipes seeded: ' . Menu::count());
    }
}
