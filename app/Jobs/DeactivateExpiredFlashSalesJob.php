<?php

namespace App\Jobs;

use App\Models\FlashSale;
use App\Services\FlashSaleStockService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DeactivateExpiredFlashSalesJob implements ShouldQueue
{
    use Queueable;

    public function handle(FlashSaleStockService $stockService): void
    {
        $expiredSales = FlashSale::live()
            ->where('ends_at', '<=', now())
            ->with('products')
            ->get();

        foreach ($expiredSales as $flashSale) {
            $stockService->clearStock($flashSale);
        }
    }
}
