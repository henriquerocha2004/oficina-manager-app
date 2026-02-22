<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_vehicle', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('client_id')
                ->constrained('client')
                ->onDelete('cascade');
            $table->foreignUlid('vehicle_id')
                ->constrained('vehicle')
                ->onDelete('cascade');
            $table->boolean('current_owner')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'vehicle_id']);
            $table->index('vehicle_id');
        });

        // Constraint único parcial: apenas 1 registro com current_owner = true por veículo
        DB::statement(
            'CREATE UNIQUE INDEX idx_current_owner_per_vehicle
            ON client_vehicle (vehicle_id)
            WHERE current_owner = true'
        );
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_current_owner_per_vehicle');
        Schema::dropIfExists('client_vehicle');
    }
};
