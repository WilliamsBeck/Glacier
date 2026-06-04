<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'Zhisheng Pusat',       'type' => 'zhisheng',        'contact' => '021-5550101', 'address' => 'Jakarta Pusat'],
            ['name' => 'CV Sumber Rasa',       'type' => 'local_supplier',  'contact' => '022-5550202', 'address' => 'Bandung'],
            ['name' => 'PT Manis Sejahtera',   'type' => 'local_supplier',  'contact' => '031-5550303', 'address' => 'Surabaya'],
            ['name' => 'Toko Lain-lain',       'type' => 'other',           'contact' => '0812-5550404', 'address' => 'Online'],
        ];

        foreach ($rows as $r) {
            Supplier::updateOrCreate(['name' => $r['name']], array_merge($r, ['is_active' => true]));
        }

        $this->command->info('Suppliers seeded: ' . Supplier::count());
    }
}
