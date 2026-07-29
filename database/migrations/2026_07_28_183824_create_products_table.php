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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique();
            $table->decimal('price',15,2);
            $table->boolean('has_variants')->default(false);
            $table->integer('stock')->default(0);
            $table->string('thumbnail');
            $table->text('description');
            $table->text('short_description')->nullable();
            $table->json('attributes')->nullable();
            $table->enum('status',['draft','pending','approved','rejected','blocked'])->default('draft');
            $table->text('admin_note')->nullable();
            $table->index('has_variants');
            $table->index('status'); // index đơn
            $table->index(['status','category_id']); // index tổ hợp             
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
