<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectRegistrationRequest;
use App\Http\Requests\Admin\StoreFlashSaleRequest;
use App\Http\Requests\Admin\SyncFlashSaleProductsRequest;
use App\Http\Requests\Admin\UpdateFlashSaleRequest;
use App\Models\FlashSale;
use App\Models\FlashSaleRegistration;
use App\Models\Product;
use App\Models\User;
use App\Notifications\FlashSaleRegistrationOpenNotification;
use App\Services\FlashSaleRegistrationService;
use App\Services\FlashSaleStockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class AdminFlashSaleController extends Controller
{
    public function __construct(
        protected FlashSaleStockService $stockService,
        protected FlashSaleRegistrationService $registrationService,
    ) {}

    public function index()
    {
        $flashSales = FlashSale::withCount('products')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $totalSales = FlashSale::count();
        $liveSales = FlashSale::live()->count();
        $upcomingSales = FlashSale::upcoming()->count();

        $availableProducts = Product::where('status', 'approved')
            ->select('id', 'name', 'price', 'stock', 'thumbnail')
            ->get();

        return view('admin.flash-sales.index', compact(
            'flashSales',
            'totalSales',
            'liveSales',
            'upcomingSales',
            'availableProducts'
        ));
    }

    public function store(StoreFlashSaleRequest $request): JsonResponse|RedirectResponse
    {
        $data = $request->validated();
        $data['status'] = $request->boolean('status', true);

        $flashSale = FlashSale::create($data);

        if ($flashSale->execution_status === 'live') {
            $this->stockService->loadStockToRedis($flashSale);
        }

        // Gui thong bao cho tat ca Seller da duoc duyet khi phien mo dang ky
        if ($flashSale->registration_deadline !== null) {
            $sellers = User::where('role', 'seller')->where('status', 'approved')->get();
            Notification::send($sellers, new FlashSaleRegistrationOpenNotification($flashSale));
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tạo phiên Flash Sale thành công!',
                'data' => $flashSale,
            ]);
        }

        return redirect()->route('admin.flash-sales.index')
            ->with('success', 'Tạo phiên Flash Sale thành công!');
    }

    public function update(UpdateFlashSaleRequest $request, FlashSale $flashSale): JsonResponse|RedirectResponse
    {
        $data = $request->validated();
        $data['status'] = $request->boolean('status', $flashSale->status);

        $flashSale->update($data);

        if ($flashSale->execution_status === 'live') {
            $this->stockService->loadStockToRedis($flashSale);
        } else {
            $this->stockService->clearStock($flashSale);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật phiên Flash Sale thành công!',
                'data' => $flashSale,
            ]);
        }

        return redirect()->route('admin.flash-sales.index')
            ->with('success', 'Cập nhật phiên Flash Sale thành công!');
    }

    public function destroy(FlashSale $flashSale): JsonResponse|RedirectResponse
    {
        if ($flashSale->execution_status === 'live') {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa phiên Flash Sale đang diễn ra!',
                ], 422);
            }

            return redirect()->back()->with('error', 'Không thể xóa phiên Flash Sale đang diễn ra!');
        }

        $this->stockService->clearStock($flashSale);
        $flashSale->products()->delete();
        $flashSale->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Xóa phiên Flash Sale thành công!',
            ]);
        }

        return redirect()->route('admin.flash-sales.index')
            ->with('success', 'Xóa phiên Flash Sale thành công!');
    }

    public function toggleStatus(FlashSale $flashSale): JsonResponse
    {
        $flashSale->update(['status' => ! $flashSale->status]);

        if ($flashSale->execution_status === 'live') {
            $this->stockService->loadStockToRedis($flashSale);
        } else {
            $this->stockService->clearStock($flashSale);
        }

        return response()->json([
            'success' => true,
            'message' => 'Thay đổi trạng thái thành công!',
            'status' => $flashSale->status,
            'execution_status' => $flashSale->execution_status,
        ]);
    }

    public function syncProducts(SyncFlashSaleProductsRequest $request, FlashSale $flashSale): JsonResponse
    {
        $productsData = $request->input('products', []);

        DB::transaction(function () use ($flashSale, $productsData) {
            $flashSale->products()->delete();

            foreach ($productsData as $item) {
                $flashSale->products()->create([
                    'product_id' => $item['product_id'],
                    'flash_sale_price' => $item['flash_sale_price'],
                    'quantity_limit' => $item['quantity_limit'],
                    'quantity_sold' => $item['quantity_sold'] ?? 0,
                ]);
            }
        });

        $flashSale->load('products.product');

        if ($flashSale->execution_status === 'live') {
            $this->stockService->loadStockToRedis($flashSale);
        }

        return response()->json([
            'success' => true,
            'message' => 'Dong bo danh sach san pham thanh cong!',
            'data' => $flashSale->products,
        ]);
    }

    // ====== REGISTRATION MANAGEMENT ======

    public function registrations(FlashSale $flashSale): JsonResponse
    {
        $registrations = $flashSale->registrations()
            ->with(['seller:id,name,email', 'product:id,name,thumbnail,price'])
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'approved' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'flash_sale' => $flashSale->only(['id', 'name', 'registration_deadline', 'starts_at']),
            'registrations' => $registrations,
            'counts' => [
                'pending' => $registrations->where('status', 'pending')->count(),
                'approved' => $registrations->where('status', 'approved')->count(),
                'rejected' => $registrations->where('status', 'rejected')->count(),
            ],
        ]);
    }

    public function approveRegistration(FlashSaleRegistration $registration): JsonResponse
    {
        if (! $registration->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'Chi co the duyet dang ky dang o trang thai cho duyet.',
            ], 422);
        }

        $flashSaleProduct = $this->registrationService->approve($registration, request()->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Duyet dang ky thanh cong. San pham da duoc them vao phien Flash Sale.',
            'flash_sale_product' => $flashSaleProduct,
        ]);
    }

    public function rejectRegistration(RejectRegistrationRequest $request, FlashSaleRegistration $registration): JsonResponse
    {
        if (! $registration->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'Chi co the tu choi dang ky dang o trang thai cho duyet.',
            ], 422);
        }

        $registration->update([
            'status' => 'rejected',
            'rejection_reason' => $request->validated()['rejection_reason'],
            'reviewed_at' => now(),
            'reviewed_by' => request()->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Da tu choi dang ky.',
        ]);
    }
}
