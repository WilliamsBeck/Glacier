<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opnames', function (Blueprint $table) {
            $table->string('opname_mode')->default('bulanan')->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('opnames', function (Blueprint $table) {
            $table->dropColumn('opname_mode');
        });
    }
};
