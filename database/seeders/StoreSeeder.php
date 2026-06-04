<?php

namespace Database\Seeders;

use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        $stores = [
            ['store_code' => 'JKT-001', 'name' => 'Glacier Senayan',     'area' => 'Jakarta'],
            ['store_code' => 'JKT-002', 'name' => 'Glacier Kelapa Gading','area' => 'Jakarta'],
            ['store_code' => 'BDG-001', 'name' => 'Glacier Dago',        'area' => 'Bandung'],
            ['store_code' => 'BDG-002', 'name' => 'Glacier Pasteur',     'area' => 'Bandung'],
            ['store_code' => 'SBY-001', 'name' => 'Glacier Tunjungan',   'area' => 'Surabaya'],
        ];

        foreach ($stores as $s) {
            Store::updateOrCreate(['store_code' => $s['store_code']], array_merge($s, [
                'is_active'        => true,
                'lead_time_days'   => 7,
                'par_days'         => 15,
                'order_cycle_days' => 15,
                'dos_window_days'  => 30,
            ]));
        }

        // Pivot store_user: admin_area per kota
        $map = [
            'jkt@glacier.id' => ['JKT-001', 'JKT-002'],
            'bdg@glacier.id' => ['BDG-001', 'BDG-002'],
            'sby@glacier.id' => ['SBY-001'],
        ];
        foreach ($map as $email => $codes) {
            $user = User::where('email', $email)->first();
            if (! $user) continue;
            $ids = Store::whereIn('store_code', $codes)->pluck('id')->all();
            $user->stores()->syncWithoutDetaching($ids);
        }

        // Super admin assign ke semua toko
        $super = User::where('role', 'super_admin')->first();
        if ($super) {
            $super->stores()->syncWithoutDetaching(Store::pluck('id')->all());
        }

        $this->command->info('Stores seeded: ' . Store::count());
    }
}
