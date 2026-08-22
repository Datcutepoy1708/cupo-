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
        Schema::table('reviews', function (Blueprint $table) {
            $table->boolean('is_reported')->default(false)->after('status');
            $table->text('report_reason')->nullable()->after('is_reported');
            $table->enum('report_status', ['none', 'pending', 'resolved', 'dismissed'])->default('none')->after('report_reason');
            $table->text('admin_note')->nullable()->after('report_status');

            $table->index('is_reported');
            $table->index('report_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['is_reported']);
            $table->dropIndex(['report_status']);
            $table->dropColumn(['is_reported', 'report_reason', 'report_status', 'admin_note']);
        });
    }
};
