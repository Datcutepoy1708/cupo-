<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\StoreRegistrationRequest;
use App\Models\FlashSale;
use App\Models\FlashSaleRegistration;
use Illuminate\Http\JsonResponse;

class SellerFlashSaleRegistrationController extends Controller
{
    /**
     * Danh sach phien Flash Sale dang mo dang ky.
     */
    public function index()
    {
        $seller = request()->user();

        $openSales = FlashSale::where('status', true)
            ->whereNotNull('registration_deadline')
            ->where('registration_deadline', '>', now())
            ->where('starts_at', '>', now())
            ->withCount([
                'registrations as my_registration_count' => function ($q) use ($seller) {
                    $q->where('seller_id', $seller->id);
                },
            ])
            ->orderBy('registration_deadline')
            ->get();

        // San pham cua seller chua dang ky vao phien nao
        $sellerProducts = $seller->products()
            ->where('status', 'approved')
            ->select('id', 'name', 'price', 'stock', 'thumbnail')
            ->get();

        return view('seller.flash-sale-registrations.index', compact('openSales', 'sellerProducts'));
    }

    /**
     * Seller gui dang ky san pham vao 1 phien Flash Sale.
     */
    public function store(StoreRegistrationRequest $request): JsonResponse
    {
        $registration = FlashSaleRegistration::create([
            'flash_sale_id' => $request->validated()['flash_sale_id'],
            'seller_id' => $request->user()->id,
            'product_id' => $request->validated()['product_id'],
            'proposed_price' => $request->validated()['proposed_price'],
            'proposed_quantity' => $request->validated()['proposed_quantity'],
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Gui dang ky thanh cong! Vui long cho Admin xet duyet.',
            'data' => $registration->load(['product:id,name,thumbnail,price', 'flashSale:id,name']),
        ]);
    }

    /**
     * Seller xem trang thai tat ca dang ky cua minh.
     */
    public function myRegistrations()
    {
        $registrations = FlashSaleRegistration::where('seller_id', request()->user()->id)
            ->with([
                'flashSale:id,name,starts_at,ends_at,registration_deadline',
                'product:id,name,thumbnail,price',
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('seller.flash-sale-registrations.mine', compact('registrations'));
    }

    /**
     * Seller huy dang ky — chi khi con pending va con truoc han chot.
     */
    public function destroy(FlashSaleRegistration $registration): JsonResponse
    {
        $seller = request()->user();

        // Ownership check — seller A khong duoc huy dang ky cua seller B
        if ((int) $registration->seller_id !== (int) $seller->id) {
            return response()->json([
                'success' => false,
                'message' => 'Ban khong co quyen thuc hien hanh dong nay.',
            ], 403);
        }

        if (! $registration->canBeCancelledBy($seller)) {
            $reason = ! $registration->isPending()
                ? 'Chi co the huy dang ky dang cho duyet.'
                : 'Da qua han chot dang ky, khong the huy.';

            return response()->json([
                'success' => false,
                'message' => $reason,
            ], 422);
        }

        $registration->delete();

        return response()->json([
            'success' => true,
            'message' => 'Da huy dang ky thanh cong.',
        ]);
    }
}
