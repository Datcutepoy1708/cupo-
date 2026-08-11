<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminStaffSeeder extends Seeder
{
    /**
     * Nạp dữ liệu mẫu cho danh sách Tài Khoản Nhân Viên Admin.
     */
    public function run(): void
    {
        // Khởi tạo các vai trò nếu chưa có
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $moderatorRole = Role::firstOrCreate(['name' => 'moderator']);
        $accountantRole = Role::firstOrCreate(['name' => 'accountant']);

        $staffs = [
            [
                'name' => 'Nguyễn Văn Quản Trị',
                'email' => 'admin@cupo.vn',
                'phone' => '0901234567',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'status' => 'active',
                'role_obj' => $adminRole,
            ],
            [
                'name' => 'Trần Thị Kiểm Duyệt',
                'email' => 'moderator@cupo.vn',
                'phone' => '0912345678',
                'password' => Hash::make('password123'),
                'role' => 'moderator',
                'status' => 'active',
                'role_obj' => $moderatorRole,
            ],
            [
                'name' => 'Lê Hoàng Kế Toán',
                'email' => 'accountant@cupo.vn',
                'phone' => '0923456789',
                'password' => Hash::make('password123'),
                'role' => 'accountant',
                'status' => 'active',
                'role_obj' => $accountantRole,
            ],
            [
                'name' => 'Phạm Quốc Hùng (Super Admin)',
                'email' => 'superadmin@cupo.vn',
                'phone' => '0934567890',
                'password' => Hash::make('password123'),
                'role' => 'super-admin',
                'status' => 'active',
                'role_obj' => $superAdminRole,
            ],
            [
                'name' => 'Đặng Thị Minh Anh',
                'email' => 'minhanh.mod@cupo.vn',
                'phone' => '0945678901',
                'password' => Hash::make('password123'),
                'role' => 'moderator',
                'status' => 'active',
                'role_obj' => $moderatorRole,
            ],
            [
                'name' => 'Vũ Đức Trọng',
                'email' => 'trong.ketoan@cupo.vn',
                'phone' => '0956789012',
                'password' => Hash::make('password123'),
                'role' => 'accountant',
                'status' => 'blocked',
                'role_obj' => $accountantRole,
            ],
        ];

        foreach ($staffs as $staffData) {
            $roleObj = $staffData['role_obj'];
            unset($staffData['role_obj']);

            $user = User::updateOrCreate(
                ['email' => $staffData['email']],
                $staffData
            );

            $user->syncRoles([$roleObj]);
        }
    }
}
