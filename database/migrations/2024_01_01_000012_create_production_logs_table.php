<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('production_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('store_id')->constrained();
            $table->foreignId('semi_finished_id')->constrained('ingredients'); // harus type semi_finished
            $table->decimal('qty_produced', 14, 4);
            $table->date('production_date');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('production_log_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('production_log_id')->constrained()->cascadeOnDelete();
            $table->foreignId('raw_ingredient_id')->constrained('ingredients');
            $table->decimal('qty_consumed', 14, 4);
            $table->decimal('price_per_base', 14, 4); // harga FIFO saat produksi
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('production_log_items');
        Schema::dropIfExists('production_logs');
    }
};
