<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredient_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();       // slug: solid, bubuk, dst
            $table->string('label');                // label tampilan: Solid, Bubuk, dst
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed kategori yang sudah ada
        $existing = [
            ['name' => 'solid',   'label' => 'Solid',   'sort_order' => 1],
            ['name' => 'bubuk',   'label' => 'Bubuk',   'sort_order' => 2],
            ['name' => 'teh',     'label' => 'Teh',     'sort_order' => 3],
            ['name' => 'sirup',   'label' => 'Sirup',   'sort_order' => 4],
            ['name' => 'selai',   'label' => 'Selai',   'sort_order' => 5],
            ['name' => 'kemasan', 'label' => 'Kemasan', 'sort_order' => 6],
        ];
        foreach ($existing as $cat) {
            DB::table('ingredient_categories')->insert(array_merge($cat, [
                'created_at' => now(), 'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredient_categories');
    }
};
