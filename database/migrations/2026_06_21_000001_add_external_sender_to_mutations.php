<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mutations', function (Blueprint $table) {
            // Nama pengirim untuk Pembelian Eksternal (diketik manual, bukan toko sistem)
            $table->string('external_sender')->nullable()->after('supplier_id');
        });
    }

    public function down(): void
    {
        Schema::table('mutations', function (Blueprint $table) {
            $table->dropColumn('external_sender');
        });
    }
};
