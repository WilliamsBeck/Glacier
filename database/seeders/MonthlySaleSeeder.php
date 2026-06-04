<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MonthlySale;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;

class MonthlySaleSeeder extends Seeder
{
    public function run(): void
    {
        $userId = User::where('role', 'super_admin')->first()?->id ?? 1;
        $stores = Store::all();
        $menus  = Menu::all();
        $now    = now();

        // 3 bulan terakhir, kedua periode
        $periods = [];
        for ($i = 0; $i < 3; $i++) {
            $d = $now->copy()->subMonthsNoOverflow($i);
            foreach (['mid_month', 'end_month'] as $pt) {
                $periods[] = ['month' => (int) $d->format('m'), 'year' => (int) $d->format('Y'), 'period_type' => $pt];
            }
        }

        foreach ($stores as $store) {
            foreach ($menus as $menu) {
                foreach ($periods as $p) {
                    MonthlySale::updateOrCreate(
                        [
                            'store_id'    => $store->id,
                            'menu_id'     => $menu->id,
                            'month'       => $p['month'],
                            'year'        => $p['year'],
                            'period_type' => $p['period_type'],
                        ],
                        [
                            'total_sold'  => random_int(150, 600),
                            'recorded_by' => $userId,
                        ]
                    );
                }
            }
        }

        $this->command->info('Monthly sales seeded: ' . MonthlySale::count());
    }
}
