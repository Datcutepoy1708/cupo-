<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions grouped by modules with Granular CRUD (View, Create, Edit, Delete, Approve/Action)
        $permissions = [
            // 1. Quản trị & Phân quyền
            'users.view', 'users.create', 'users.edit', 'users.delete', 'roles.manage',

            // 2. Quản lý Gian hàng (Seller)
            'sellers.view', 'sellers.approve', 'sellers.block', 'sellers.delete',

            // 3. Quản lý Sản phẩm
            'products.view', 'products.create', 'products.edit', 'products.approve', 'products.delete',

            // 4. Quản lý Danh mục
            'categories.view', 'categories.create', 'categories.edit', 'categories.delete',

            // 5. Quản lý Banner & Quảng cáo
            'banners.view', 'banners.create', 'banners.edit', 'banners.delete',

            // 6. Tài chính & Rút tiền
            'withdrawals.view', 'withdrawals.approve', 'reports.view',

            // 7. Tranh chấp & Khiếu nại
            'disputes.view', 'disputes.edit', 'disputes.resolve',

            // Seller permissions
            'manage own products', 'manage own orders', 'view own dashboard', 'manage own coupons',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 1. Super Admin Role (All Permissions)
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin']);
        $superAdminRole->givePermissionTo(Permission::all());

        // 2. Admin Role (All Admin Permissions)
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo([
            'users.view', 'users.create', 'users.edit', 'users.delete', 'roles.manage',
            'sellers.view', 'sellers.approve', 'sellers.block', 'sellers.delete',
            'products.view', 'products.create', 'products.edit', 'products.approve', 'products.delete',
            'categories.view', 'categories.create', 'categories.edit', 'categories.delete',
            'banners.view', 'banners.create', 'banners.edit', 'banners.delete',
            'withdrawals.view', 'withdrawals.approve', 'reports.view',
            'disputes.view', 'disputes.edit', 'disputes.resolve',
        ]);

        // 3. Moderator Role (Kiểm duyệt viên)
        $moderatorRole = Role::firstOrCreate(['name' => 'moderator']);
        $moderatorRole->givePermissionTo([
            'products.view', 'products.approve', 'products.delete',
            'sellers.view', 'sellers.approve', 'sellers.block',
            'disputes.view', 'disputes.edit', 'disputes.resolve',
        ]);

        // 4. Accountant Role (Kế toán)
        $accountantRole = Role::firstOrCreate(['name' => 'accountant']);
        $accountantRole->givePermissionTo([
            'withdrawals.view', 'withdrawals.approve', 'reports.view',
        ]);

        // 5. Seller Role
        $sellerRole = Role::firstOrCreate(['name' => 'seller']);
        $sellerRole->givePermissionTo([
            'manage own products', 'manage own orders', 'view own dashboard', 'manage own coupons',
        ]);

        // 6. Customer Role
        $customerRole = Role::firstOrCreate(['name' => 'customer']);
    }
}
