<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng theo dõi gian hàng: Customer theo dõi (follow) Seller.
     * Unique (user_id, seller_profile_id) đảm bảo không follow trùng.
     */
    public function up(): void
    {
        Schema::create('shop_follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('seller_profile_id')
                ->constrained('seller_profiles')
                ->cascadeOnDelete();
            $table->timestamp('followed_at')->useCurrent();

            // 1 user chỉ follow 1 shop 1 lần
            $table->unique(['user_id', 'seller_profile_id']);

            // Index để đếm nhanh số người theo dõi của 1 shop
            $table->index('seller_profile_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_follows');
    }
};
