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
        Schema::table('waste_log_items', function (Blueprint $table) {
            $table->boolean('is_rework')->default(false)->after('subtotal_loss');
        });
    }

    public function down(): void
    {
        Schema::table('waste_log_items', function (Blueprint $table) {
            $table->dropColumn('is_rework');
        });
    }
};
