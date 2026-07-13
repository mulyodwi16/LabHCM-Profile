<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['admin', 'dosen', 'member', 'alumni'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $admin = User::firstOrCreate(
            ['email' => 'admin@hcm.com'],
            [
                'name' => 'HCM Admin',
                'password' => 'admin@hcm',
                'email_verified_at' => now(),
            ]
        );

        $admin->syncRoles(['admin']);
        $admin->profile()->firstOrCreate([]);
    }
}
