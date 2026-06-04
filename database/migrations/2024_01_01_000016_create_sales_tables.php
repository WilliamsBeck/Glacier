<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('monthly_sales', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('store_id')->constrained();
            $table->foreignId('menu_id')->constrained();
            $table->integer('total_sold');
            $table->tinyInteger('month');
            $table->smallInteger('year');
            $table->enum('period_type', ['mid_month', 'end_month']);
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();
            $table->unique(['store_id', 'menu_id', 'month', 'year', 'period_type'], 'ms_unique');
        });

        Schema::create('hpp_monthly_reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('store_id')->constrained();
            $table->foreignId('ingredient_id')->constrained();
            $table->tinyInteger('month');
            $table->smallInteger('year');
            $table->enum('period_type', ['mid_month', 'end_month']);
            $table->date('date_from');
            $table->date('date_to');
            $table->decimal('ideal_usage_base', 14, 4)->default(0);
            $table->decimal('actual_usage_base', 14, 4)->default(0);
            $table->decimal('avg_price_per_base', 14, 4)->default(0);
            $table->decimal('hpp_ideal', 14, 4)->default(0);
            $table->decimal('hpp_actual', 14, 4)->default(0);
            $table->decimal('variance_qty', 14, 4)->default(0);
            $table->decimal('variance_amount', 14, 4)->default(0);
            $table->timestamps();
            $table->unique(['store_id', 'ingredient_id', 'month', 'year', 'period_type'], 'hpp_m_unique');
        });
    }
    public function down(): void {
        Schema::dropIfExists('hpp_monthly_reports');
        Schema::dropIfExists('monthly_sales');
    }
};
