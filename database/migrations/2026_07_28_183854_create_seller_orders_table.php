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
        Schema::create('seller_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->restrictOnDelete();
            $table->decimal('sub_total',15,2);
            $table->decimal('shipping_fee',15,2)->default(0);
            $table->decimal('discount_amount',15,2)->default(0);
            $table->decimal('grand_total',15,2);
            $table->decimal('commission_amount',15,2);
            $table->enum('status',['pending','confirmed','shipping','completed','cancelled'])->default('pending');
            $table->string('tracking_number',100)->nullable();
            $table->timestamps();
            
            $table->index('status');
            $table->index('tracking_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_orders');
    }
};
