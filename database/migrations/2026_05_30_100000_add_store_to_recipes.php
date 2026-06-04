<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            // NULL = resep default (berlaku semua toko); ID = resep khusus toko itu.
            $table->foreignId('store_id')->nullable()->after('menu_id')->constrained()->nullOnDelete();
            $table->index(['menu_id', 'store_id', 'effective_from']);
        });
    }

    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropIndex(['menu_id', 'store_id', 'effective_from']);
            $table->dropForeign(['store_id']);
            $table->dropColumn('store_id');
        });
    }
};
