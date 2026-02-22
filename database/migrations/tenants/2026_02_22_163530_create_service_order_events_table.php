<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_order_events', function (Blueprint $table) {
            $table->ulid(column: 'id')->primary();
            $table->foreignUlid('service_order_id')->constrained('service_orders');
            $table->foreignId('user_id')->constrained('users');
            $table->enum('event_type', ['status_changed', 'item_added', 'item_removed', 'diagnosis_updated', 'payment_received', 'note_added']);
            $table->text('description');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('service_order_id');
            $table->index('user_id');
            $table->index('event_type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_order_events');
    }
};
