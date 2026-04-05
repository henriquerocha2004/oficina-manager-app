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
        Schema::create('service_order_photos', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('service_order_id')->constrained('service_orders')->onDelete('cascade');
            $table->string('file_path');
            $table->string('original_filename');
            $table->unsignedInteger('file_size');
            $table->string('mime_type');
            $table->unsignedSmallInteger('width')->nullable();
            $table->unsignedSmallInteger('height')->nullable();
            $table->unsignedTinyInteger('display_order')->default(0);
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index('service_order_id');
            $table->index('uploaded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_order_photos');
    }
};
