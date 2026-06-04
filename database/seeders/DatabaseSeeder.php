<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Buat Super Admin
        User::updateOrCreate(
            ['email' => 'admin@mixue.id'],
            [
                'name'     => 'Super Admin',
                'email'    => 'admin@mixue.id',
                'password' => Hash::make('Admin123!'),
                'role'     => 'super_admin',
            ]
        );

        echo "✅ Super Admin dibuat:\n";
        echo "   Email   : admin@mixue.id\n";
        echo "   Password: Admin123!\n";
    }
}
