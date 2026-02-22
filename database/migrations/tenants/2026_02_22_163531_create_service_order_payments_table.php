<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_order_payments', function (Blueprint $table) {
            $table->ulid(column: 'id')->primary();
            $table->foreignUlid('service_order_id')->constrained('service_orders');
            $table->foreignId('received_by')->constrained('users');
            $table->enum('payment_method', ['cash', 'credit_card', 'debit_card', 'pix', 'bank_transfer', 'check']);
            $table->decimal('amount', 10, 2);
            $table->timestamp('paid_at');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('service_order_id');
            $table->index('received_by');
            $table->index('payment_method');
            $table->index('paid_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_order_payments');
    }
};
