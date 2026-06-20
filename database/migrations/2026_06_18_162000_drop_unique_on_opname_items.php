<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opname_items', function (Blueprint $table) {
            $table->dropUnique('opname_items_opname_ing_pkg_unique');
        });
    }

    public function down(): void
    {
        Schema::table('opname_items', function (Blueprint $table) {
            $table->unique(['opname_id', 'ingredient_id', 'packaging_id'], 'opname_items_opname_ing_pkg_unique');
        });
    }
};
