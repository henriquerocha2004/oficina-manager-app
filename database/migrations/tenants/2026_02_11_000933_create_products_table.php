<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->string('name');
            $table->text('description')->nullable();
            $table->string('sku', 50)->unique()->nullable();
            $table->string('barcode', 50)->unique()->nullable();
            $table->string('manufacturer')->nullable();

            $table->enum('category', [
                'engine',
                'suspension',
                'brakes',
                'electrical',
                'filters',
                'fluids',
                'tires',
                'body_parts',
                'interior',
                'tools',
                'other',
            ])->default('other');

            $table->integer('min_stock_level')->nullable();
            $table->enum('unit', ['unit', 'liter', 'kg', 'meter', 'box'])->default('unit');

            $table->decimal('unit_price', 10, 2);
            $table->decimal('suggested_price', 10, 2)->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index('sku');
            $table->index('barcode');
            $table->index('category');
            $table->index('is_active');
            $table->index('min_stock_level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
