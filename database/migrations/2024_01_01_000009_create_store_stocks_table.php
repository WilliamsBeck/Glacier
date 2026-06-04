<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('store_stocks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->decimal('stock_balance', 14, 4)->default(0);
            $table->decimal('min_stock_base', 14, 4)->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->unique(['store_id', 'ingredient_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('store_stocks'); }
};
