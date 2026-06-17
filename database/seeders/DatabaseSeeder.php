<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            StoreSeeder::class,
            SupplierSeeder::class,
            MenuCategorySeeder::class,
            IngredientSeeder::class,
            IngredientCompositionSeeder::class,
            MenuSeeder::class,
            StoreStockSeeder::class,
            MonthlySaleSeeder::class,
            TransactionSeeder::class,
            HppReportSeeder::class,
            DailyUsageSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('=== Glacier dummy data ready ===');
        $this->command->info('Login: admin@glacier.id / Admin123!');
    }
}
