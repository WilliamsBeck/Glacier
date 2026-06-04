<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('stock_ledger', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('store_id')->constrained();
            $table->foreignId('ingredient_id')->constrained();
            $table->date('movement_date');
            $table->enum('movement_type', [
                'purchase_in',
                'transfer_in',
                'transfer_out',
                'production_in',
                'production_out',
                'sale_deduction',
                'waste',
                'opname_adjustment',
            ]);
            $table->decimal('qty_change', 14, 4);   // positif = masuk, negatif = keluar
            $table->string('reference_type');        // 'Mutation', 'ProductionLog', dll
            $table->bigInteger('reference_id');
            $table->decimal('balance_after', 14, 4);
            $table->string('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('created_at')->useCurrent();
            $table->index(['store_id', 'ingredient_id', 'movement_date']);
        });
    }
    public function down(): void { Schema::dropIfExists('stock_ledger'); }
};
