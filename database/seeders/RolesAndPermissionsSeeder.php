<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Dashboard
            'view dashboard',
            'view admin dashboard',
            // Content
            'manage content',
            'manage pages',
            'manage hero',
            'manage features',
            'manage services',
            'manage testimonials',
            'manage faqs',
            // Media & Layout
            'manage media',
            'manage sliders',
            'manage banners',
            'manage menus',
            // Messages
            'manage messages',
            // System
            'manage settings',
            'manage users',
            'manage roles',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Super Admin — full unrestricted access
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdminRole->givePermissionTo(Permission::all());

        // Admin — content management, no user/role management
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo([
            'view dashboard',
            'view admin dashboard',
            'manage content',
            'manage pages',
            'manage hero',
            'manage features',
            'manage services',
            'manage testimonials',
            'manage faqs',
            'manage media',
            'manage sliders',
            'manage banners',
            'manage menus',
            'manage messages',
        ]);

        // User — basic dashboard access only
        $userRole = Role::firstOrCreate(['name' => 'user']);
        $userRole->givePermissionTo(['view dashboard']);
    }
}
