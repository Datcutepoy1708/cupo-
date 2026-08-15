<?php

namespace App\Jobs;

use App\Models\FlashSaleProduct;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Redis;

class SyncFlashSaleStockToDatabase implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $productId) {}

    public function handle(): void
    {
        $redisStock = Redis::get("flash_sale_stock:{$this->productId}");

        if ($redisStock === null) {
            return;
        }

        $flashSaleProduct = FlashSaleProduct::where('product_id', $this->productId)
            ->whereHas('flashSale', function ($q) {
                $q->live();
            })
            ->first();

        if ($flashSaleProduct) {
            $sold = max(0, $flashSaleProduct->quantity_limit - (int) $redisStock);
            $flashSaleProduct->update(['quantity_sold' => $sold]);
        }
    }
}
