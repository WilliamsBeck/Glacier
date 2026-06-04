<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('opnames', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('store_id')->constrained();
            $table->date('opname_date');
            $table->tinyInteger('period_month');
            $table->smallInteger('period_year');
            $table->enum('period_type', ['mid_month', 'end_month']);
            $table->enum('status', ['draft', 'completed', 'approved'])->default('draft');
            $table->foreignId('performed_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('opname_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('opname_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained();
            $table->foreignId('packaging_id')->nullable()->constrained('ingredient_packagings')->nullOnDelete();
            $table->decimal('system_qty', 14, 4);        // dari stock_ledger
            $table->integer('physical_crate')->nullable();
            $table->integer('physical_pack')->nullable();
            $table->decimal('physical_base', 14, 4)->nullable();
            $table->decimal('physical_qty', 14, 4);      // total dalam base unit
            $table->decimal('variance', 14, 4);          // physical_qty - system_qty
            $table->timestamps();
            $table->unique(['opname_id', 'ingredient_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('opname_items');
        Schema::dropIfExists('opnames');
    }
};
