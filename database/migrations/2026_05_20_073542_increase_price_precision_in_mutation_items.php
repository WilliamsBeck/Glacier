<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Naikkan presisi harga supaya konversi harga/dus → harga/gram → harga/dus
     * tidak menghasilkan selisih 1 rupiah akibat pembulatan 4 desimal.
     */
    public function up(): void
    {
        Schema::table('mutation_items', function (Blueprint $table) {
            $table->decimal('price_per_base', 20, 8)->change();
            $table->decimal('selling_price_per_base', 20, 8)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('mutation_items', function (Blueprint $table) {
            $table->decimal('price_per_base', 14, 4)->change();
            $table->decimal('selling_price_per_base', 14, 4)->nullable()->change();
        });
    }
};
