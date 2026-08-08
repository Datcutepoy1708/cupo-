<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng trung gian: 1 gian hàng đăng ký kinh doanh nhiều danh mục sản phẩm.
     * Ví dụ: Shop "ABC" vừa bán Thời trang, vừa bán Phụ kiện.
     */
    public function up(): void
    {
        Schema::create('seller_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_profile_id')
                ->constrained('seller_profiles')
                ->cascadeOnDelete();
            $table->foreignId('category_id')
                ->constrained('categories')
                ->cascadeOnDelete();
            $table->timestamps();

            // Mỗi cặp (shop, danh mục) chỉ tồn tại 1 lần
            $table->unique(['seller_profile_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_categories');
    }
};
