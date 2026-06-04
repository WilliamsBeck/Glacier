<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
        });

        // Isi username dari email (bagian sebelum @) untuk user yang sudah ada
        DB::table('users')->get()->each(function ($user) {
            $base     = explode('@', $user->email)[0];
            $username = preg_replace('/[^a-z0-9_]/', '', strtolower($base)) ?: 'user' . $user->id;
            // pastikan unik
            $final = $username;
            $i = 1;
            while (DB::table('users')->where('username', $final)->where('id', '!=', $user->id)->exists()) {
                $final = $username . $i++;
            }
            DB::table('users')->where('id', $user->id)->update(['username' => $final]);
        });

        // Setelah diisi semua, buat tidak nullable
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('username');
        });
    }
};
