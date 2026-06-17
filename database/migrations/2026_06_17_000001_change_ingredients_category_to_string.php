<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Kolom ingredients.category sebelumnya enum tetap yang tidak sinkron dengan
 * master "Kategori Bahan Baku" (ingredient_categories) yang dinamis & bisa diedit.
 * Ubah menjadi VARCHAR agar nilai kategori mengikuti master, bukan daftar hardcoded.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE ingredients MODIFY category VARCHAR(100) NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE ingredients MODIFY category ENUM('bubuk','teh','sirup','selai','solid','kemasan') NULL");
    }
};
