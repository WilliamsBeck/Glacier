<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('mutations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('reference_no')->unique();
            $table->enum('type', [
                'opening_stock',
                'purchase_zhisheng',
                'purchase_supplier',
                'transfer_internal',
                'transfer_external',
                'sale_internal',
                'sale_external',
            ]);
            $table->foreignId('source_store_id')->nullable()->constrained('stores')->nullOnDelete();
            $table->foreignId('destination_store_id')->nullable()->constrained('stores')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('invoice_no')->nullable();
            $table->date('transaction_date');
            $table->date('delivery_date')->nullable();
            $table->enum('status', ['draft', 'confirmed', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('mutations'); }
};
