<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Super Admin
        $superAdmin = User::firstOrCreate([
            'email' => 'superadmin@example.com',
        ], [
            'name' => 'Super Admin',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        $superAdmin->syncRoles(['super_admin']);

        // Create Admin User
        $admin = User::firstOrCreate([
            'email' => 'admin@example.com',
        ], [
            'name' => 'Admin Manager',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        $admin->syncRoles(['admin']);

        // Create Default User
        $user = User::firstOrCreate([
            'email' => 'user@example.com',
        ], [
            'name' => 'John Student',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        $user->syncRoles(['user']);

        // Create additional test users
        $testUsers = [
            ['name' => 'Sarah Wilson', 'email' => 'sarah@example.com'],
            ['name' => 'Michael Chen', 'email' => 'michael@example.com'],
            ['name' => 'Priya Sharma', 'email' => 'priya@example.com'],
            ['name' => 'David Taylor', 'email' => 'david@example.com'],
            ['name' => 'Emily Rodriguez', 'email' => 'emily@example.com'],
        ];

        foreach ($testUsers as $userData) {
            $testUser = User::firstOrCreate([
                'email' => $userData['email'],
            ], [
                'name' => $userData['name'],
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]);
            $testUser->syncRoles(['user']);
        }
    }
}
