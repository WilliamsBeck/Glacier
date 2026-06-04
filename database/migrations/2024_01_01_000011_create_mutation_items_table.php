<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('mutation_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('mutation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained();
            $table->foreignId('packaging_id')->nullable()->constrained('ingredient_packagings')->nullOnDelete();
            $table->integer('qty_crate')->nullable();
            $table->integer('qty_pack')->nullable();
            $table->decimal('qty_base', 14, 4)->nullable();
            $table->decimal('total_in_base', 14, 4);         // hasil konversi final
            $table->decimal('price_per_base', 14, 4);        // harga per satuan terkecil
            $table->decimal('selling_price_per_base', 14, 4)->nullable(); // hanya untuk tipe sale
            $table->decimal('cost_subtotal', 14, 4);         // total_in_base × price_per_base
            $table->decimal('remaining_qty', 14, 4);         // sisa untuk FIFO
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('mutation_items'); }
};
