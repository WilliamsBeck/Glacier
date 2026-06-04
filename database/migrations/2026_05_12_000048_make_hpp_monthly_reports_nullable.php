<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hpp_monthly_reports', function (Blueprint $table) {
            // Kolom aktual & variance boleh null (belum ada SO akhir yang approved)
            $table->decimal('actual_usage_base', 14, 4)->nullable()->change();
            $table->decimal('hpp_actual',        14, 4)->nullable()->change();
            $table->decimal('variance_qty',      14, 4)->nullable()->change();
            $table->decimal('variance_amount',   14, 4)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('hpp_monthly_reports', function (Blueprint $table) {
            $table->decimal('actual_usage_base', 14, 4)->nullable(false)->default(0)->change();
            $table->decimal('hpp_actual',        14, 4)->nullable(false)->default(0)->change();
            $table->decimal('variance_qty',      14, 4)->nullable(false)->default(0)->change();
            $table->decimal('variance_amount',   14, 4)->nullable(false)->default(0)->change();
        });
    }
};
