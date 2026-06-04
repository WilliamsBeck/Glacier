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
        Schema::table('stores', function (Blueprint $table) {
            // Hapus kolom jadwal order yang terlalu detail & sering berubah
            $table->dropColumn(['order_frequency', 'order_day_1', 'order_day_2']);

            // Tambah par_days: "stok harus cukup X hari" — set manual per toko
            // Default null = belum diset (tampilkan DOS saja tanpa status)
            $table->tinyInteger('par_days')->unsigned()->nullable()
                  ->after('lead_time_days')
                  ->comment('Par level dalam hari — stok harus cukup X hari ke depan');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('par_days');
            $table->tinyInteger('order_frequency')->unsigned()->default(1)->after('is_active');
            $table->tinyInteger('order_day_1')->unsigned()->default(1)->after('order_frequency');
            $table->tinyInteger('order_day_2')->unsigned()->nullable()->after('order_day_1');
        });
    }
};
