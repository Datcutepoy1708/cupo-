<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCouponRequest;
use App\Http\Requests\Admin\UpdateCouponRequest;
use App\Models\Coupon;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminCouponController extends Controller
{
    /**
     * Danh sach Ma giam gia cho Admin.
     * Browser request -> tra ve Blade view (admin.coupons.index)
     * AJAX request    -> tra ve JSON danh sach kem meta thong ke
     */
    public function index(Request $request): View|JsonResponse
    {
        if (! $request->wantsJson()) {
            $sellers = User::query()
                ->where('role', 'seller')
                ->with('sellerProfile')
                ->orderBy('name')
                ->get();

            return view('admin.coupons.index', compact('sellers'));
        }

        $status = $request->query('status');
        $type = $request->query('type');
        $scope = $request->query('scope');
        $sellerId = $request->query('seller_id');
        $keyword = $request->query('search');

        $now = Carbon::now();

        $coupons = Coupon::query()
            ->with(['seller.sellerProfile'])
            ->when($status === 'active', function ($q) use ($now) {
                $q->where('status', true)
                    ->where(function ($sub) use ($now) {
                        $sub->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
                    })
                    ->where(function ($sub) use ($now) {
                        $sub->whereNull('expires_at')->orWhere('expires_at', '>=', $now);
                    })
                    ->whereColumn('used_count', '<', 'usage_limit');
            })
            ->when($status === 'upcoming', function ($q) use ($now) {
                $q->where('status', true)
                    ->whereNotNull('starts_at')
                    ->where('starts_at', '>', $now);
            })
            ->when($status === 'expired', function ($q) use ($now) {
                $q->where(function ($sub) use ($now) {
                    $sub->where(function ($exp) use ($now) {
                        $exp->whereNotNull('expires_at')->where('expires_at', '<', $now);
                    })->orWhereColumn('used_count', '>=', 'usage_limit');
                });
            })
            ->when($status === 'inactive', function ($q) {
                $q->where('status', false);
            })
            ->when($type, function ($q) use ($type) {
                $q->where('type', $type);
            })
            ->when($scope === 'platform', function ($q) {
                $q->whereNull('seller_id');
            })
            ->when($scope === 'shop', function ($q) {
                $q->whereNotNull('seller_id');
            })
            ->when($sellerId, function ($q) use ($sellerId) {
                $q->where('seller_id', $sellerId);
            })
            ->when($keyword, function ($q) use ($keyword) {
                $q->where(function ($sub) use ($keyword) {
                    $sub->where('code', 'like', '%'.$keyword.'%')
                        ->orWhereHas('seller.sellerProfile', function ($sellerQuery) use ($keyword) {
                            $sellerQuery->where('shop_name', 'like', '%'.$keyword.'%');
                        });
                });
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        // Thong ke cho Stat Cards va Tab Badges
        $allCount = Coupon::count();

        $activeCount = Coupon::query()
            ->where('status', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', $now);
            })
            ->whereColumn('used_count', '<', 'usage_limit')
            ->count();

        $upcomingCount = Coupon::query()
            ->where('status', true)
            ->whereNotNull('starts_at')
            ->where('starts_at', '>', $now)
            ->count();

        $expiredCount = Coupon::query()
            ->where(function ($q) use ($now) {
                $q->where(function ($sub) use ($now) {
                    $sub->whereNotNull('expires_at')->where('expires_at', '<', $now);
                })->orWhereColumn('used_count', '>=', 'usage_limit');
            })
            ->count();

        $inactiveCount = Coupon::where('status', false)->count();
        $totalUsedCount = (int) Coupon::sum('used_count');

        return response()->json(array_merge($coupons->toArray(), [
            'meta' => [
                'total_all' => $allCount,
                'total_active' => $activeCount,
                'total_upcoming' => $upcomingCount,
                'total_expired' => $expiredCount,
                'total_inactive' => $inactiveCount,
                'total_used' => $totalUsedCount,
            ],
        ]));
    }

    /**
     * POST /admin/coupons
     * Tao ma giam gia moi
     */
    public function store(StoreCouponRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['code'] = strtoupper(trim($data['code']));
        $data['status'] = $request->boolean('status', true);
        $data['starts_at'] = $request->filled('starts_at') ? Carbon::parse($request->input('starts_at')) : null;
        $data['expires_at'] = $request->filled('expires_at') ? Carbon::parse($request->input('expires_at')) : null;
        $data['max_discount'] = ($data['type'] === 'percentage' && $request->filled('max_discount'))
            ? $data['max_discount']
            : null;
        $data['min_order_amount'] = $data['min_order_amount'] ?? 0;
        $data['used_count'] = 0;

        $coupon = Coupon::create($data);
        $coupon->load(['seller.sellerProfile']);

        return response()->json([
            'success' => true,
            'message' => 'Tao ma giam gia moi thanh cong.',
            'data' => $coupon,
        ], 201);
    }

    /**
     * GET /admin/coupons/{coupon}
     * Chi tiet ma giam gia kem lich su su dung
     */
    public function show(Coupon $coupon): JsonResponse
    {
        $coupon->load([
            'seller.sellerProfile',
            'usages.user',
            'usages.order',
        ]);

        return response()->json([
            'success' => true,
            'data' => $coupon,
        ]);
    }

    /**
     * PUT/PATCH /admin/coupons/{coupon}
     * Cap nhat ma giam gia
     */
    public function update(UpdateCouponRequest $request, Coupon $coupon): JsonResponse
    {
        $data = $request->validated();
        $data['code'] = strtoupper(trim($data['code']));
        $data['status'] = $request->boolean('status', true);
        $data['starts_at'] = $request->filled('starts_at') ? Carbon::parse($request->input('starts_at')) : null;
        $data['expires_at'] = $request->filled('expires_at') ? Carbon::parse($request->input('expires_at')) : null;
        $data['max_discount'] = ($data['type'] === 'percentage' && $request->filled('max_discount'))
            ? $data['max_discount']
            : null;
        $data['min_order_amount'] = $data['min_order_amount'] ?? 0;

        $coupon->update($data);
        $coupon->load(['seller.sellerProfile']);

        return response()->json([
            'success' => true,
            'message' => 'Cap nhat ma giam gia thanh cong.',
            'data' => $coupon,
        ]);
    }

    /**
     * DELETE /admin/coupons/{coupon}
     * Xoa ma giam gia
     */
    public function destroy(Coupon $coupon): JsonResponse
    {
        $coupon->delete();

        return response()->json([
            'success' => true,
            'message' => 'Da xoa ma giam gia thanh cong.',
        ]);
    }

    /**
     * PATCH /admin/coupons/{coupon}/toggle-status
     * Bat/tat nhanh trang thai kich hoat
     */
    public function toggleStatus(Coupon $coupon): JsonResponse
    {
        $coupon->status = ! $coupon->status;
        $coupon->save();

        return response()->json([
            'success' => true,
            'message' => $coupon->status ? 'Da kich hoat ma giam gia.' : 'Da vo hieu hoa ma giam gia.',
            'status' => $coupon->status,
        ]);
    }

    /**
     * POST /admin/coupons/bulk-status
     * Thay doi trang thai hang loat
     */
    public function bulkStatus(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:coupons,id'],
            'status' => ['required', 'boolean'],
        ]);

        $ids = $request->input('ids');
        $status = $request->boolean('status');

        Coupon::whereIn('id', $ids)->update(['status' => $status]);

        return response()->json([
            'success' => true,
            'message' => 'Da cap nhat trang thai cho '.count($ids).' ma giam gia.',
        ]);
    }

    /**
     * POST /admin/coupons/bulk-delete
     * Xoa hang loat ma giam gia
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:coupons,id'],
        ]);

        $ids = $request->input('ids');
        Coupon::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Da xoa '.count($ids).' ma giam gia.',
        ]);
    }
}
