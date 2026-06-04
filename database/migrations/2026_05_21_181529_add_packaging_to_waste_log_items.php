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
        Schema::table('waste_log_items', function (Blueprint $table) {
            $table->foreignId('packaging_id')->nullable()->after('source_qty')
                  ->constrained('ingredient_packagings')->nullOnDelete();
            $table->integer('qty_crate')->nullable()->after('packaging_id');
            $table->integer('qty_pack')->nullable()->after('qty_crate');
        });
    }

    public function down(): void
    {
        Schema::table('waste_log_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('packaging_id');
            $table->dropColumn(['qty_crate', 'qty_pack']);
        });
    }
};
