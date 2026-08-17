<?php

namespace Tests\Feature;

use App\Jobs\SyncFlashSaleStockToDatabase;
use App\Services\FlashSaleStockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class FlashSaleStockServiceTest extends TestCase
{
    use RefreshDatabase;

    protected FlashSaleStockService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FlashSaleStockService;
    }

    public function test_reserve_stock_decrements_redis_atomically()
    {
        Queue::fake([SyncFlashSaleStockToDatabase::class]);

        Redis::shouldReceive('decr')
            ->once()
            ->with('flash_sale_stock:100')
            ->andReturn(4);

        $result = $this->service->reserveStock(100);

        $this->assertTrue($result);
        Queue::assertPushed(SyncFlashSaleStockToDatabase::class);
    }

    public function test_reserve_stock_fails_and_rolls_back_when_out_of_stock()
    {
        Redis::shouldReceive('decr')
            ->once()
            ->with('flash_sale_stock:100')
            ->andReturn(-1);

        Redis::shouldReceive('incr')
            ->once()
            ->with('flash_sale_stock:100')
            ->andReturn(0);

        $result = $this->service->reserveStock(100);

        $this->assertFalse($result);
    }
}
