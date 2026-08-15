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
        if (! Schema::hasTable('shop_follows')) {
            Schema::create('shop_follows', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('seller_profile_id')->constrained('seller_profiles')->cascadeOnDelete();
                $table->timestamp('followed_at')->useCurrent();
                $table->timestamps();

                $table->unique(['user_id', 'seller_profile_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_follows');
    }
};
