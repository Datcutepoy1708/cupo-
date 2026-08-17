<?php

namespace App\Services;

use App\Jobs\SyncFlashSaleStockToDatabase;
use App\Models\FlashSale;
use Illuminate\Support\Facades\Redis;

class FlashSaleStockService
{
    public function reserveStock(int $productId): bool
    {
        $remaining = Redis::decr("flash_sale_stock:{$productId}");

        if ($remaining < 0) {
            Redis::incr("flash_sale_stock:{$productId}"); // rollback

            return false;
        }

        SyncFlashSaleStockToDatabase::dispatch($productId);

        return true;
    }

    public function loadStockToRedis(FlashSale $flashSale): void
    {
        foreach ($flashSale->products as $item) {
            $remaining = max(0, $item->quantity_limit - $item->quantity_sold);
            Redis::set("flash_sale_stock:{$item->product_id}", $remaining);
        }
    }

    public function clearStock(FlashSale $flashSale): void
    {
        foreach ($flashSale->products as $item) {
            Redis::del("flash_sale_stock:{$item->product_id}");
        }
    }
}
