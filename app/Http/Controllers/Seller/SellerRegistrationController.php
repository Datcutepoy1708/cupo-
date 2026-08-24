<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\SellerRegistrationRequest;
use App\Models\Category;
use App\Models\SellerProfile;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SellerRegistrationController extends Controller
{
    /**
     * Hiển thị trang đăng ký bán hàng.
     */
    public function create(): View|RedirectResponse
    {
        $user = auth()->user();
        $sellerProfile = $user->sellerProfile;

        if ($sellerProfile && $sellerProfile->status === 'approved') {
            return redirect()->route('seller.dashboard');
        }

        $categories = Category::tree()->get();

        return view('seller.register', compact('sellerProfile', 'categories'));
    }

    /**
     * Tiếp nhận hồ sơ đăng ký / nộp lại hồ sơ của Seller.
     * Sử dụng updateOrCreate để hỗ trợ mượt mà luồng nộp lại hồ sơ sau khi bị từ chối.
     */
    public function store(SellerRegistrationRequest $request): RedirectResponse
    {
        $user = $request->user();

        DB::transaction(function () use ($user, $request) {
            $dob = preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $request->date_of_birth)
                ? Carbon::createFromFormat('d/m/Y', $request->date_of_birth)
                : Carbon::parse($request->date_of_birth);

            $user->update([
                'role' => 'seller',
                'phone' => $request->phone ?? $user->phone,
                'date_of_birth' => $dob->format('Y-m-d'),
            ]);

            $existingProfile = $user->sellerProfile;
            $slug = $existingProfile?->slug ?? (Str::slug($request->shop_name).'-'.Str::random(5));

            $autoApprove = (setting('auto_approve_sellers', '0') == '1');
            $status = $autoApprove ? 'approved' : 'pending';

            $sellerProfile = SellerProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'shop_name' => $request->shop_name,
                    'business_type' => $request->input('business_type', 'personal'),
                    'slug' => $slug,
                    'address' => $request->address,
                    'description' => $request->description,
                    'national_id' => $request->national_id,
                    'status' => $status,
                    'admin_note' => null,  // Xóa lý do từ chối trước đó
                ]
            );

            if ($request->filled('category_ids')) {
                $sellerProfile->categories()->sync($request->category_ids);
            }

            // Gửi thông báo cho Ban Quản Trị
            $admins = User::whereIn('role', ['super-admin', 'admin'])->get();
            if ($admins->isNotEmpty()) {
                Notification::send($admins, new GeneralNotification(
                    'Gian hàng mới đăng ký',
                    "Shop '{$request->shop_name}' vừa nộp hồ sơ đăng ký kinh doanh.",
                    route('admin.sellers.index'),
                    'fa-solid fa-store',
                    'info'
                ));
            }
        });

        if (setting('auto_approve_sellers', '0') == '1') {
            return redirect()->route('seller.dashboard')->with('success', 'Chào mừng bạn! Hồ sơ gian hàng đã được tự động phê duyệt.');
        }

        return redirect()->route('seller.pending-approval')->with('success', 'Đã nộp hồ sơ đăng ký gian hàng thành công! Vui lòng chờ Ban Quản Trị phê duyệt.');
    }

    /**
     * Trang thông báo trạng thái phê duyệt hồ sơ gian hàng.
     */
    public function pendingApproval(): View|RedirectResponse
    {
        $user = auth()->user();
        $sellerProfile = $user->sellerProfile;

        if ($sellerProfile && $sellerProfile->status === 'approved') {
            return redirect()->route('seller.dashboard');
        }

        return view('seller.pending-approval', compact('sellerProfile'));
    }
}
