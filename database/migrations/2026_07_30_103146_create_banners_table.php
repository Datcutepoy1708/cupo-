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
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');                         // Tên nội bộ: "Banner Tết 2025"
            $table->string('image_path');                    // Đường dẫn ảnh banner
            $table->string('link_url')->nullable();          // Nhấn vào banner đi đâu
            $table->enum('position', [
                'homepage_hero',   // Banner lớn đầu trang chủ (slider)
                'homepage_mid',    // Banner giữa trang chủ
                'category_top',    // Banner đầu trang danh mục
                'sidebar',         // Banner cột bên
            ])->default('homepage_hero');
            $table->unsignedTinyInteger('sort_order')->default(0); // Thứ tự hiển thị
            $table->boolean('is_active')->default(true);     // Bật/tắt nhanh
            $table->timestamp('starts_at')->nullable();      // Ngày bắt đầu hiển thị
            $table->timestamp('ends_at')->nullable();        // Ngày kết thúc
            $table->timestamps();

            $table->index(['position', 'is_active']);        // Index để query nhanh
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
