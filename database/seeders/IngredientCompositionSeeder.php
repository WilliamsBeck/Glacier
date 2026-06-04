<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\IngredientComposition;
use Illuminate\Database\Seeder;

class IngredientCompositionSeeder extends Seeder
{
    public function run(): void
    {
        $byName = fn (string $n) => Ingredient::where('name', $n)->first()?->id;

        // Komposisi per 1 satuan base hasil (gram untuk Base Es Krim, ml untuk Base Teh)
        $compositions = [
            'Base Es Krim Vanilla' => [
                ['Susu Cair UHT',  0.6000],
                ['Gula Pasir',     0.1500],
                ['Tepung Es Krim', 0.2000],
                ['Krimer Bubuk',   0.0500],
            ],
            'Base Es Krim Coklat' => [
                ['Susu Cair UHT',  0.5500],
                ['Gula Pasir',     0.1500],
                ['Tepung Es Krim', 0.2000],
                ['Bubuk Coklat',   0.1000],
            ],
            'Base Teh Manis' => [
                ['Teh Hitam',      0.0200],
                ['Gula Pasir',     0.1000],
                ['Garam',          0.0010],
            ],
        ];

        foreach ($compositions as $parent => $children) {
            $pid = $byName($parent);
            if (! $pid) continue;
            foreach ($children as [$childName, $qty]) {
                $cid = $byName($childName);
                if (! $cid) continue;
                IngredientComposition::updateOrCreate(
                    ['parent_id' => $pid, 'child_id' => $cid],
                    ['qty_needed' => $qty]
                );
            }
        }

        $this->command->info('Ingredient compositions seeded.');
    }
}
