<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingredient_compositions', function (Blueprint $table) {
            // Dari decimal(12,4) → decimal(16,8) agar 3000/11000 = 0.27272727
            // tersimpan cukup presisi dan hasil perkalian tidak meleset jauh.
            $table->decimal('qty_needed', 16, 8)->change();
        });
    }

    public function down(): void
    {
        Schema::table('ingredient_compositions', function (Blueprint $table) {
            $table->decimal('qty_needed', 12, 4)->change();
        });
    }
};
