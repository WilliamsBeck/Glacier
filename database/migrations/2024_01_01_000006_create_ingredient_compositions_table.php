<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ingredient_compositions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('parent_id')->constrained('ingredients')->cascadeOnDelete();
            $table->foreignId('child_id')->constrained('ingredients')->cascadeOnDelete();
            $table->decimal('qty_needed', 12, 4); // qty bahan baku per 1 satuan semi_finished
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('ingredient_compositions'); }
};
