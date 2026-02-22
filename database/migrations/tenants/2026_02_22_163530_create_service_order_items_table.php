<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_order_items', function (Blueprint $table) {
            $table->ulid(column: 'id')->primary();
            $table->foreignUlid('service_order_id')->constrained('service_orders');
            $table->enum('type', ['service', 'part'])->default('service');
            $table->foreignUlid('service_id')->nullable()->constrained('services');
            $table->foreignUlid('product_id')->nullable()->constrained('products');
            $table->string('description');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('service_order_id');
            $table->index('type');
            $table->index('service_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_order_items');
    }
};
