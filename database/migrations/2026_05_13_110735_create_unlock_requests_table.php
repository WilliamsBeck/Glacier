<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unlock_requests', function (Blueprint $table) {
            $table->id();

            // Resource yang diminta unlock-nya
            $table->string('resource_type');   // mutation | opname | monthly_sale
            $table->unsignedBigInteger('resource_id')->nullable(); // ID record spesifik (null = per periode)

            // Konteks periode
            $table->foreignId('store_id')->constrained();
            $table->unsignedTinyInteger('resource_month');
            $table->unsignedSmallInteger('resource_year');
            $table->string('resource_period_type', 20)->nullable(); // end_month | mid_month

            // Pemohon
            $table->foreignId('requested_by')->constrained('users');
            $table->text('reason');

            // Status
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_notes')->nullable();

            $table->timestamps();

            $table->index(['resource_type', 'resource_id']);
            $table->index(['store_id', 'resource_month', 'resource_year', 'status'], 'ur_store_period_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unlock_requests');
    }
};
