<?php

namespace App\Services;

use App\Models\FlashSaleProduct;
use App\Models\FlashSaleRegistration;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;

class FlashSaleRegistrationService
{
    public function __construct(protected FlashSaleStockService $stockService) {}

    /**
     * Duyet 1 dang ky Flash Sale.
     * - Kiem tra khong trung lap trong flash_sale_products (bao loi 422 thay vi ghi de im lang).
     * - Trong DB::transaction: update dang ky + tao dong flash_sale_products.
     * - Neu phien dang Live, reload stock len Redis.
     */
    public function approve(FlashSaleRegistration $registration, int $reviewerId): FlashSaleProduct
    {
        $alreadyExists = FlashSaleProduct::where('flash_sale_id', $registration->flash_sale_id)
            ->where('product_id', $registration->product_id)
            ->exists();

        if ($alreadyExists) {
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'San pham nay da co trong phien Flash Sale (duoc them thu cong hoac tu mot dang ky khac). Khong the duyet trung.',
                ], 422)
            );
        }

        $flashSaleProduct = DB::transaction(function () use ($registration, $reviewerId) {
            $registration->update([
                'status' => 'approved',
                'reviewed_at' => now(),
                'reviewed_by' => $reviewerId,
            ]);

            return FlashSaleProduct::create([
                'flash_sale_id' => $registration->flash_sale_id,
                'product_id' => $registration->product_id,
                'flash_sale_price' => $registration->proposed_price,
                'quantity_limit' => $registration->proposed_quantity,
                'quantity_sold' => 0,
            ]);
        });

        // Neu phien dang Live, reload stock len Redis ngay lap tuc
        $registration->flashSale->load('products');
        if ($registration->flashSale->execution_status === 'live') {
            $this->stockService->loadStockToRedis($registration->flashSale);
        }

        return $flashSaleProduct;
    }
}
