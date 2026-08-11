<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminStaffController extends Controller
{
    /**
     * POST /admin/staff
     * Tạo tài khoản nhân viên mới
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6'],
            'role_name' => ['required', 'string', 'exists:roles,name'],
            'status' => ['required', Rule::in(['active', 'blocked'])],
        ], [
            'name.required' => 'Vui lòng nhập họ tên nhân viên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.unique' => 'Email này đã tồn tại trong hệ thống.',
            'password.required' => 'Vui lòng nhập mật khẩu khởi tạo.',
            'password.min' => 'Mật khẩu phải chứa ít nhất 6 ký tự.',
            'role_name.required' => 'Vui lòng chọn chức vụ cho nhân viên.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => $validated['role_name'],
            'status' => $validated['status'],
        ]);

        $user->assignRole($validated['role_name']);

        return response()->json([
            'status' => 'success',
            'message' => 'Tạo tài khoản nhân viên mới thành công!',
            'data' => $user->load('roles'),
        ], 201);
    }

    /**
     * PUT/PATCH /admin/staff/{user}
     * Cập nhật thông tin cá nhân & Chức vụ nhân viên
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'role_name' => ['required', 'string', 'exists:roles,name'],
            'status' => ['required', Rule::in(['active', 'blocked'])],
            'password' => ['nullable', 'string', 'min:6'],
        ], [
            'name.required' => 'Vui lòng nhập họ tên nhân viên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.unique' => 'Email này đã được sử dụng.',
            'role_name.required' => 'Vui lòng chọn chức vụ.',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role_name'],
            'status' => $validated['status'],
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);
        $user->syncRoles([$validated['role_name']]);

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật thông tin nhân viên thành công!',
            'data' => $user->fresh('roles'),
        ]);
    }

    /**
     * POST /admin/staff/{user}/reset-password
     * Đặt lại mật khẩu nhân viên
     */
    public function resetPassword(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:6'],
        ], [
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu mới phải từ 6 ký tự.',
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Đã đặt lại mật khẩu cho nhân viên {$user->name} thành công!",
        ]);
    }

    /**
     * DELETE /admin/staff/{user}
     * Xóa tài khoản nhân viên
     */
    public function destroy(User $user): JsonResponse
    {
        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'Không thể xóa chính tài khoản đang đăng nhập!',
            ], 400);
        }

        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Đã xóa tài khoản nhân viên thành công!',
        ]);
    }
}
