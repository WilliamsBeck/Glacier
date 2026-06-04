<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tambah index terlebih dulu agar FK opname_id tetap punya backing index
        // setelah unique lama di-drop.
        Schema::table('opname_items', function (Blueprint $table) {
            $table->index('opname_id', 'opname_items_opname_id_idx');
        });

        Schema::table('opname_items', function (Blueprint $table) {
            // Drop unique lama: (opname_id, ingredient_id)
            $table->dropUnique(['opname_id', 'ingredient_id']);
            // Unique baru: (opname_id, ingredient_id, packaging_id)
            $table->unique(
                ['opname_id', 'ingredient_id', 'packaging_id'],
                'opname_items_opname_ing_pkg_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('opname_items', function (Blueprint $table) {
            $table->dropUnique('opname_items_opname_ing_pkg_unique');
            $table->unique(['opname_id', 'ingredient_id']);
        });
    }
};
