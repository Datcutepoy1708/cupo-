<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_shipping_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_order_id')->constrained('seller_orders')->cascadeOnDelete();
            $table->foreignId('carrier_id')->nullable()->constrained('shipping_carriers')->nullOnDelete();
            $table->string('status', 50); // order_placed, preparing, picked_up, sorting_hub, in_transit, delivering, delivered, failed
            $table->string('title', 255);
            $table->string('location', 255)->nullable();
            $table->text('description')->nullable();
            $table->timestamp('event_time')->useCurrent();
            $table->timestamps();

            $table->index(['seller_order_id', 'event_time']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_shipping_logs');
    }
};
