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
        Schema::table('monthly_revenues', function (Blueprint $table) {
            // Existing records semua end_month, jadi default('end_month') sudah benar.
            $table->string('period_type', 20)->default('end_month')->after('year');
        });
    }

    public function down(): void
    {
        Schema::table('monthly_revenues', function (Blueprint $table) {
            $table->dropColumn('period_type');
        });
    }
};
