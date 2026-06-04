<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opname_items', function (Blueprint $table) {
            // Harga per base yang diinput manual saat opname (dipakai bootstrap stok awal
            // bila belum ada harga pembelian sebelumnya). Null = harga ikut data yang ada.
            $table->decimal('price_per_base', 16, 6)->nullable()->after('variance');
        });
    }

    public function down(): void
    {
        Schema::table('opname_items', function (Blueprint $table) {
            $table->dropColumn('price_per_base');
        });
    }
};
