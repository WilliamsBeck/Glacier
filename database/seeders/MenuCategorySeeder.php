<?php

namespace Database\Seeders;

use App\Models\MenuCategory;
use Illuminate\Database\Seeder;

class MenuCategorySeeder extends Seeder
{
    public function run(): void
    {
        $cats = [
            ['name' => 'Ice Cream',  'sort_order' => 1],
            ['name' => 'Milk Tea',   'sort_order' => 2],
            ['name' => 'Fruit Tea',  'sort_order' => 3],
            ['name' => 'Coffee',     'sort_order' => 4],
            ['name' => 'Topping',    'sort_order' => 5],
        ];

        foreach ($cats as $c) {
            MenuCategory::updateOrCreate(['name' => $c['name']], $c);
        }

        $this->command->info('Menu categories seeded: ' . MenuCategory::count());
    }
}
