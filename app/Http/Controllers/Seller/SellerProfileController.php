<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\SellerOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SellerProfileController extends Controller
{
    public function index(Request $request)
    {
        $shop = Auth::user()->sellerProfile;

        abort_if(! $shop, 404, 'Bạn chưa đăng ký gian hàng.');

        if ($shop->status === 'blocked') {
            return view('client.seller-store.blocked', compact('shop'));
        }

        $allCategories = collect();
        $allCategoriesForSelection = collect();

        if ($shop->status === 'approved') {
            $shop->product_count = Product::where('seller_id', $shop->user_id)->count();

            $shop->pending_orders = SellerOrder::where('seller_id', $shop->user_id)
                ->where('status', 'pending')
                ->count();

            $shop->revenue_month = SellerOrder::where('seller_id', $shop->user_id)
                ->where('status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('grand_total');

            $shop->recentOrders = SellerOrder::with(['order.user'])
                ->where('seller_id', $shop->user_id)
                ->latest()
                ->take(5)
                ->get();

            $shop->pendingOrdersList = SellerOrder::with(['order.user', 'items'])
                ->where('seller_id', $shop->user_id)
                ->where('status', 'pending')
                ->latest()
                ->get();

            $shop->products = Product::where('seller_id', $shop->user_id)
                ->with('category')
                ->latest()
                ->get();

            $shop->reviews = Review::whereHas('product', function ($q) use ($shop) {
                $q->where('seller_id', $shop->user_id);
            })
                ->latest()
                ->get();

            $shop->review_count = $shop->reviews->count();
            $shop->rating = round($shop->reviews->avg('rating') ?? 0, 1);

            $shop->followers_count = $shop->followers()->count();

            $shop->load(['categories' => function ($q) {
                $q->where('status', true);
            }]);

            $registeredCategoryIds = $shop->categories->pluck('id')->toArray();

            if (! empty($registeredCategoryIds)) {
                $allCategories = Category::where('status', true)
                    ->whereNull('parent_id')
                    ->where(function ($q) use ($registeredCategoryIds) {
                        $q->whereIn('id', $registeredCategoryIds)
                            ->orWhereHas('children', function ($sub) use ($registeredCategoryIds) {
                                $sub->whereIn('id', $registeredCategoryIds);
                            });
                    })
                    ->with(['children' => function ($q) use ($registeredCategoryIds) {
                        $q->where('status', true)
                            ->where(function ($sub) use ($registeredCategoryIds) {
                                $sub->whereIn('id', $registeredCategoryIds)
                                    ->orWhereIn('parent_id', $registeredCategoryIds);
                            })
                            ->orderBy('name');
                    }])
                    ->orderBy('name')
                    ->get();
            } else {
                $allCategories = collect();
            }

            // Lấy tất cả ngành hàng trong hệ thống để chọn đăng ký thêm
            $allCategoriesForSelection = Category::where('status', true)
                ->whereNull('parent_id')
                ->with(['children' => function ($q) {
                    $q->where('status', true)->orderBy('name');
                }])
                ->orderBy('name')
                ->get();
        }

        return view('client.seller-store.index', compact('shop', 'allCategories', 'allCategoriesForSelection'));
    }

    /**
     * Xử lý đăng ký bổ sung ngành hàng kinh doanh cho gian hàng.
     */
    public function requestCategories(Request $request): RedirectResponse
    {
        $request->validate([
            'request_categories' => ['required', 'array'],
            'request_categories.*' => ['exists:categories,id'],
        ], [
            'request_categories.required' => 'Vui lòng chọn ít nhất 1 ngành hàng muốn đăng ký bổ sung.',
            'request_categories.array' => 'Dữ liệu ngành hàng không hợp lệ.',
            'request_categories.*.exists' => 'Ngành hàng đã chọn không tồn tại trong hệ thống.',
        ]);

        $shop = Auth::user()->sellerProfile;

        abort_if(! $shop, 404, 'Bạn chưa đăng ký gian hàng.');

        $shop->categories()->syncWithoutDetaching($request->input('request_categories'));

        return back()->with('success', 'Đã đăng ký bổ sung ngành hàng kinh doanh thành công cho cửa hàng của bạn!');
    }
}
