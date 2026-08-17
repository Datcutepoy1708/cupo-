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
        Schema::create('seller_support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->enum('category', [
                'account_blocked',
                'withdrawal_issue',
                'product_rejected',
                'commission_fee',
                'other',
            ])->default('other');
            $table->string('subject', 255);
            $table->text('message');
            $table->string('attachment', 255)->nullable();
            $table->enum('status', ['open', 'in_review', 'resolved', 'closed'])->default('open');
            $table->text('admin_response')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_support_tickets');
    }
};
