<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@staffing.com')],
            [
                'name'              => 'Super Admin',
                'password'          => Hash::make(env('ADMIN_PASSWORD', 'ChangeMe2026!')),
                'is_admin'          => true,
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('admin');
    }
}
