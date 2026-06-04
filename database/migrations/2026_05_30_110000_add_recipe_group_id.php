<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            // 1 versi resep (banyak toko) berbagi 1 group_id.
            $table->uuid('recipe_group_id')->nullable()->after('store_id')->index();
        });

        // Backfill: setiap tuple (menu_id, store_id, effective_from) yang ada di data lama
        // dianggap 1 versi → dapat 1 uuid baru.
        $groups = DB::table('recipes')
            ->whereNull('recipe_group_id')
            ->select('menu_id', 'store_id', 'effective_from')
            ->distinct()->get();
        foreach ($groups as $g) {
            DB::table('recipes')
                ->where('menu_id', $g->menu_id)
                ->where('effective_from', $g->effective_from)
                ->when($g->store_id, fn($q) => $q->where('store_id', $g->store_id),
                                    fn($q) => $q->whereNull('store_id'))
                ->update(['recipe_group_id' => (string) Str::uuid()]);
        }
    }

    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropIndex(['recipe_group_id']);
            $table->dropColumn('recipe_group_id');
        });
    }
};
