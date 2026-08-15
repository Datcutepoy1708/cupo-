<?php

namespace App\Jobs;

use App\Models\FlashSale;
use App\Services\FlashSaleStockService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ActivateFlashSalesJob implements ShouldQueue
{
    use Queueable;

    public function handle(FlashSaleStockService $stockService): void
    {
        $upcomingSales = FlashSale::upcoming()
            ->where('starts_at', '<=', now())
            ->with('products')
            ->get();

        foreach ($upcomingSales as $flashSale) {
            $stockService->loadStockToRedis($flashSale);
        }
    }
}
