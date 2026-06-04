<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('daily_usages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('store_id')->constrained();
            $table->foreignId('ingredient_id')->constrained();
            $table->date('usage_date');
            $table->decimal('qty_pack', 14, 4)->default(0);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->unique(['store_id', 'ingredient_id', 'usage_date']);
        });
    }
    public function down(): void { Schema::dropIfExists('daily_usages'); }
};
