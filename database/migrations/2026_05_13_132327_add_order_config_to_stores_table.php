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
            // Berapa kali order per bulan: 1 atau 2
            $table->tinyInteger('order_frequency')->unsigned()->default(1)
                  ->after('is_active')
                  ->comment('1 = 1x/bulan, 2 = 2x/bulan');

            // Tanggal di bulan untuk order pertama (1–28)
            $table->tinyInteger('order_day_1')->unsigned()->default(1)
                  ->after('order_frequency')
                  ->comment('Tanggal order ke-1 setiap bulan');

            // Tanggal di bulan untuk order kedua (opsional, hanya jika 2x/bulan)
            $table->tinyInteger('order_day_2')->unsigned()->nullable()
                  ->after('order_day_1')
                  ->comment('Tanggal order ke-2 (jika 2x/bulan)');

            // Estimasi hari perjalanan dari order sampai barang tiba
            $table->tinyInteger('lead_time_days')->unsigned()->default(7)
                  ->after('order_day_2')
                  ->comment('Hari dari order sampai barang tiba di toko');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['order_frequency', 'order_day_1', 'order_day_2', 'lead_time_days']);
        });
    }
};
