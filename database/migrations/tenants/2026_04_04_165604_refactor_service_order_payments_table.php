<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_order_payments', function (Blueprint $table) {
            $table->enum('type', ['payment', 'refund'])->default('payment')->after('id');
            $table->tinyInteger('installments')->unsigned()->nullable()->after('notes');
            $table->index('type');

            $table->dropForeign(['refunded_by']);
            $table->dropColumn(['refunded_at', 'refunded_by', 'refund_reason']);
        });
    }

    public function down(): void
    {
        Schema::table('service_order_payments', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropColumn(['type', 'installments']);

            $table->timestamp('refunded_at')->nullable()->after('paid_at');
            $table->foreignId('refunded_by')->nullable()->constrained('users');
            $table->text('refund_reason')->nullable()->after('refunded_by');
        });
    }
};
