<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('daily_edit_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->tinyInteger('request_month');
            $table->smallInteger('request_year');
            $table->unsignedBigInteger('requested_by');
            $table->text('reason');
            $table->tinyInteger('extra_days');                     // berapa hari tambahan yg diminta
            $table->string('status', 20)->default('pending');      // pending / approved / rejected
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->date('new_lock_until')->nullable();            // diisi saat approve
            $table->text('admin_notes')->nullable();               // catatan reviewer
            $table->timestamps();

            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_edit_requests');
    }
};
