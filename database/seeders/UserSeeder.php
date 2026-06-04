<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@glacier.id'],
            [
                'name'     => 'Super Admin',
                'username' => 'superadmin',
                'password' => Hash::make('Admin123!'),
                'role'     => 'super_admin',
            ]
        );

        $areas = [
            ['name' => 'Admin Jakarta',  'username' => 'admin_jkt', 'email' => 'jkt@glacier.id'],
            ['name' => 'Admin Bandung',  'username' => 'admin_bdg', 'email' => 'bdg@glacier.id'],
            ['name' => 'Admin Surabaya', 'username' => 'admin_sby', 'email' => 'sby@glacier.id'],
        ];

        foreach ($areas as $u) {
            User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name'     => $u['name'],
                    'username' => $u['username'],
                    'password' => Hash::make('Admin123!'),
                    'role'     => 'admin_area',
                ]
            );
        }

        $this->command->info('Users seeded (password: Admin123!)');
    }
}
