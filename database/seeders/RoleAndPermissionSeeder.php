<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'manage users',
            'manage sellers',
            'approve products',
            'manage categories',
            'manage disputes',
            'manage withdrawals',
            'manage system settings',
            
            'manage own products',
            'manage own orders',
            'view own dashboard',
            'manage own coupons',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $sellerRole = Role::firstOrCreate(['name' => 'seller']);
        $sellerRole->givePermissionTo([
            'manage own products',
            'manage own orders',
            'view own dashboard',
            'manage own coupons',
        ]);

        $customerRole = Role::firstOrCreate(['name' => 'customer']);
    }
}
