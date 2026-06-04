<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('waste_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('store_id')->constrained();
            $table->date('waste_date');
            $table->text('notes')->nullable();
            $table->decimal('total_loss_amount', 14, 4)->default(0);
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('waste_log_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('waste_log_id')->constrained()->cascadeOnDelete();
            $table->enum('source_type', ['raw', 'semi_finished']); // apa yang diinput user
            $table->foreignId('source_ingredient_id')->constrained('ingredients');
            $table->decimal('source_qty', 14, 4);
            $table->foreignId('ingredient_id')->constrained('ingredients'); // selalu raw
            $table->decimal('qty_base', 14, 4);       // qty raw setelah ekspansi komposisi
            $table->decimal('price_per_base', 14, 4); // harga FIFO saat waste
            $table->decimal('subtotal_loss', 14, 4);  // qty_base × price_per_base
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('waste_log_items');
        Schema::dropIfExists('waste_logs');
    }
};
