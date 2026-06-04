<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_stocks', function (Blueprint $table) {
            // Berapa hari stok yang ingin dipertahankan (ditetapkan user)
            $table->unsignedTinyInteger('par_level_days')->nullable()->after('min_stock_base');
        });
    }

    public function down(): void
    {
        Schema::table('store_stocks', function (Blueprint $table) {
            $table->dropColumn('par_level_days');
        });
    }
};
