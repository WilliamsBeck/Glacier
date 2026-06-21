<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hpp_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->string('period_type', 20); // mid_month | end_month
            // Ringkasan angka kunci (untuk query cepat tanpa decode JSON)
            $table->decimal('omset', 18, 2)->default(0);
            $table->decimal('hpp_ideal', 18, 2)->default(0);
            $table->decimal('hpp_aktual', 18, 2)->nullable();
            // Salinan lengkap hasil perhitungan (summary + menuRows + ingredientRows)
            $table->longText('payload');
            $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['store_id', 'month', 'year', 'period_type'], 'hpp_snap_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hpp_snapshots');
    }
};
