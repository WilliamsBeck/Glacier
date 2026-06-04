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
            // Window DOS: berapa hari terakhir untuk hitung rata-rata pemakaian (7/14/30)
            $table->tinyInteger('dos_window_days')->unsigned()->default(30)->after('order_cycle_days');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('dos_window_days');
        });
    }
};
