<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('recipes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->decimal('qty_usage', 12, 4);
            $table->string('unit'); // gram / ml / pcs
            $table->date('effective_from');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            // Tidak ada unique — satu menu boleh punya banyak versi resep
        });
    }
    public function down(): void { Schema::dropIfExists('recipes'); }
};
