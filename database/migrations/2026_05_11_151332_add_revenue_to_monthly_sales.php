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
        Schema::table('monthly_sales', function (Blueprint $table) {
            $table->decimal('total_revenue', 14, 2)->default(0)->after('total_sold');
        });
    }

    public function down(): void
    {
        Schema::table('monthly_sales', function (Blueprint $table) {
            $table->dropColumn('total_revenue');
        });
    }
};
