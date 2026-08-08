<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function show(Request $request): View
    {
        $orders = $request->user()->orders()
            ->with(['sellerOrders.items.product', 'sellerOrders.seller.sellerProfile'])
            ->latest()
            ->get();

        return view('client.profile.index', [
            'user' => $request->user(),
            'orders' => $orders,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Fill các field thông thường, bỏ qua avatar (xử lý riêng)
        $user->fill($request->safe()->except('avatar'));

        // Xử lý upload avatar nếu có file được gửi lên
        if ($request->hasFile('avatar')) {
            // Xóa ảnh cũ trong storage để tránh tích lũy file rác
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            // Lưu ảnh mới: storage/app/public/avatars/{user_id}/{filename}
            $user->avatar = $request->file('avatar')
                ->store('avatars/'.$user->id, 'public');
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.show')
            ->with('status', 'profile-updated')
            ->with('active_tab', 'personal');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'confirmed', Password::defaults(), 'different:current_password'],
        ], [], [
            'current_password' => 'mật khẩu hiện tại',
            'new_password' => 'mật khẩu mới',
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return back()
            ->with('status', 'password-updated')
            ->with('active_tab', 'changePassword');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
