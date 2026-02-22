<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_orders', function (Blueprint $table) {
            $table->ulid(column: 'id')->primary();
            $table->string('order_number')->unique();
            $table->foreignUlid('vehicle_id')->constrained('vehicle');
            $table->foreignUlid('client_id')->constrained('client');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('technician_id')->nullable()->constrained('users');
            $table->enum('status', ['draft', 'waiting_approval', 'approved', 'in_progress', 'waiting_payment', 'completed', 'cancelled'])->default('draft');
            $table->text('diagnosis')->nullable();
            $table->text('observations')->nullable();
            $table->decimal('total_parts', 10, 2)->default(0);
            $table->decimal('total_services', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('order_number');
            $table->index('vehicle_id');
            $table->index('client_id');
            $table->index('status');
            $table->index('created_by');
            $table->index('technician_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_orders');
    }
};
