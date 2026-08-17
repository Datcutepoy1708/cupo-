<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flash_sale_registrations', function (Blueprint $table) {
            $table->id();

            // cascadeOnDelete: dang ky mat nghia khi phien bi xoa
            $table->foreignId('flash_sale_id')->constrained()->cascadeOnDelete();

            // restrictOnDelete: lich su dang ky, khong duoc xoa theo user
            $table->foreignId('seller_id')->constrained('users')->restrictOnDelete();

            // restrictOnDelete: lich su, khong duoc xoa theo san pham
            $table->foreignId('product_id')->constrained()->restrictOnDelete();

            $table->decimal('proposed_price', 12, 2);
            $table->unsignedInteger('proposed_quantity');

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            // Bat buoc khi status = rejected (Rule 16 AGENT.md)
            $table->text('rejection_reason')->nullable();

            $table->timestamp('reviewed_at')->nullable();

            // nullOnDelete: admin co the bi xoa, dang ky van giu lai lich su
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // 1 phien chi co 1 dang ky cho 1 san pham
            $table->unique(['flash_sale_id', 'product_id']);

            $table->index('seller_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flash_sale_registrations');
    }
};
