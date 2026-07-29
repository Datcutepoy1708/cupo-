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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number',50)->unique();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->decimal('total_item_amount',15,2);
            $table->decimal('total_shipping_fee',15,2)->default(0);
            $table->decimal('total_discount',15,2)->default(0);
            $table->decimal('grand_total',15,2);
            $table->enum('payment_method',['cod','vnpay','momo']);
            $table->enum('payment_status',['pending','paid','failed','refunded'])->default('pending');
            $table->string('shipping_name');
            $table->string('shipping_phone',20);
            $table->string('shipping_address',500);
            $table->string('notes')->nullable();
            $table->timestamps();
            $table->index('payment_method');
            $table->index('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
