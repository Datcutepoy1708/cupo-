<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminRoleController extends Controller
{
    /**
     * GET /admin/roles
     * Trang Quản lý Phân Quyền & Vai Trò
     */
    public function index(): View
    {
        return view('admin.roles.index');
    }

    /**
     * GET /admin/roles/data
     * JSON API lấy danh sách Vai trò, Ma trận Quyền và Danh sách Nhân viên Admin
     */
    public function data(): JsonResponse
    {
        // 1. Danh sách Vai trò (kèm mảng quyền & số lượng user)
        $roles = Role::with('permissions')
            ->withCount('users')
            ->orderBy('id', 'asc')
            ->get();

        // 2. Nhóm tất cả Quyền hạn theo Module chức năng CRUD
        $rawPermissions = Permission::orderBy('name', 'asc')->get();

        $groupedPermissions = [
            'Quản trị & Phân quyền' => $rawPermissions->filter(fn ($p) => Str::startsWith($p->name, ['users.', 'roles.']))->values(),
            'Quản lý Gian hàng (Seller)' => $rawPermissions->filter(fn ($p) => Str::startsWith($p->name, 'sellers.'))->values(),
            'Quản lý Sản phẩm' => $rawPermissions->filter(fn ($p) => Str::startsWith($p->name, 'products.'))->values(),
            'Quản lý Danh mục' => $rawPermissions->filter(fn ($p) => Str::startsWith($p->name, 'categories.'))->values(),
            'Quản lý Banner & Quảng cáo' => $rawPermissions->filter(fn ($p) => Str::startsWith($p->name, 'banners.'))->values(),
            'Tài chính & Rút tiền' => $rawPermissions->filter(fn ($p) => Str::startsWith($p->name, ['withdrawals.', 'reports.']))->values(),
            'Tranh chấp & Khiếu nại' => $rawPermissions->filter(fn ($p) => Str::startsWith($p->name, 'disputes.'))->values(),
            'Quản lý Vận chuyển & Đối tác' => $rawPermissions->filter(fn ($p) => Str::startsWith($p->name, 'shipping.'))->values(),
            'Quyền Người Bán (Seller)' => $rawPermissions->filter(fn ($p) => Str::startsWith($p->name, 'manage own'))->values(),
        ];

        // 3. Danh sách Nhân viên Admin (Chỉ lấy tài khoản Nhân viên, loại bỏ Khách hàng & Seller)
        $users = User::with('roles')
            ->whereIn('role', ['admin', 'super-admin', 'moderator', 'accountant'])
            ->select('id', 'name', 'email', 'phone', 'role', 'status', 'created_at')
            ->latest('id')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'roles' => $roles,
                'grouped_permissions' => $groupedPermissions,
                'all_permissions' => $rawPermissions,
                'users' => $users,
            ],
        ]);
    }

    /**
     * POST /admin/roles
     * Tạo Chức vụ / Vai trò mới
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ], [
            'name.required' => 'Vui lòng nhập tên chức vụ.',
            'name.unique' => 'Tên chức vụ này đã tồn tại.',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        if (! empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return response()->json([
            'message' => 'Tạo chức vụ mới thành công!',
            'data' => $role->load('permissions'),
        ], 201);
    }

    /**
     * GET /admin/roles/{role}
     * Chi tiết 1 Vai trò
     */
    public function show(Role $role): JsonResponse
    {
        return response()->json([
            'data' => $role->load('permissions'),
        ]);
    }

    /**
     * PUT/PATCH /admin/roles/{role}
     * Cập nhật Chức vụ & Danh sách Quyền
     */
    public function update(Request $request, Role $role): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255', 'unique:roles,name,'.$role->id],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        // Bảo vệ không cho đổi tên vai trò hệ thống gốc
        $protectedRoles = ['super-admin', 'admin', 'seller', 'customer'];
        if (in_array($role->name, $protectedRoles) && isset($validated['name']) && $validated['name'] !== $role->name) {
            return response()->json([
                'message' => 'Không thể đổi tên các vai trò hệ thống cố định ('.$role->name.').',
            ], 400);
        }

        if (isset($validated['name'])) {
            $role->update(['name' => $validated['name']]);
        }

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return response()->json([
            'message' => 'Cập nhật phân quyền chức vụ thành công!',
            'data' => $role->fresh('permissions'),
        ]);
    }

    /**
     * DELETE /admin/roles/{role}
     * Xóa Chức vụ
     */
    public function destroy(Role $role): JsonResponse
    {
        $protectedRoles = ['super-admin', 'admin', 'seller', 'customer'];
        if (in_array($role->name, $protectedRoles)) {
            return response()->json([
                'message' => 'Không thể xóa các vai trò hệ thống mặc định ('.$role->name.').',
            ], 400);
        }

        $role->delete();

        return response()->json([
            'message' => 'Đã xóa chức vụ thành công!',
        ]);
    }

    /**
     * POST /admin/roles/assign-user
     * Gán Chức vụ / Vai trò cho Nhân viên Admin
     */
    public function assignUserRole(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role_name' => ['required', 'exists:roles,name'],
        ], [
            'user_id.required' => 'Vui lòng chọn tài khoản nhân viên.',
            'role_name.required' => 'Vui lòng chọn chức vụ.',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $user->syncRoles([$validated['role_name']]);

        // Cập nhật trường role của bảng Users
        $user->update(['role' => $validated['role_name']]);

        return response()->json([
            'message' => "Đã gán chức vụ '{$validated['role_name']}' cho nhân viên {$user->name} thành công!",
            'data' => $user->load('roles'),
        ]);
    }
}
