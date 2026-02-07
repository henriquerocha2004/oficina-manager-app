<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle', function (Blueprint $table) {
            $table->ulid(column:'id')->primary();
            $table->string('license_plate')->unique();
            $table->string('brand');
            $table->string('model');
            $table->integer('year');
            $table->string('color')->nullable();
            $table->string('vin')->nullable()->unique();
            $table->string('fuel')->nullable();
            $table->string('transmission')->nullable();
            $table->integer('mileage')->nullable();
            $table->string('cilinder_capacity')->nullable();
            $table->foreignUlid('client_id')->constrained('client')->onDelete('cascade');
            $table->enum('vehicle_type', ['car', 'motorcycle'])->default('car');
            $table->text('observations')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('license_plate');
            $table->index('vin');
            $table->index('client_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle');
    }
};
