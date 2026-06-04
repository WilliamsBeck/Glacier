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
        Schema::create('monthly_revenues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained();
            $table->tinyInteger('month');
            $table->smallInteger('year');
            $table->decimal('total_revenue', 16, 2)->default(0);
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();
            $table->unique(['store_id', 'month', 'year'], 'mr_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_revenues');
    }
};
