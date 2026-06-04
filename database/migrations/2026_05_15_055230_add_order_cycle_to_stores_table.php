<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            // Siklus order: seberapa sering order (misal setiap 15 hari)
            $table->tinyInteger('order_cycle_days')->unsigned()->nullable()->after('lead_time_days');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('order_cycle_days');
        });
    }
};
