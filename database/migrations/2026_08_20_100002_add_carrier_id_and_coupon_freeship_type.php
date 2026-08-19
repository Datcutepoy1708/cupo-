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
        Schema::table('seller_orders', function (Blueprint $table) {
            $table->foreignId('carrier_id')->nullable()->after('tracking_number')->constrained('shipping_carriers')->nullOnDelete();
        });

        Schema::table('coupons', function (Blueprint $table) {
            $table->string('type', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seller_orders', function (Blueprint $table) {
            $table->dropForeign(['carrier_id']);
            $table->dropColumn('carrier_id');
        });

        Schema::table('coupons', function (Blueprint $table) {
            $table->enum('type', ['fixed_amount', 'percentage'])->change();
        });
    }
};
