<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->table('vehicle', function (Blueprint $table): void {
            $table->dropForeign(['client_id']);
            $table->dropColumn('client_id');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->table('vehicle', function (Blueprint $table): void {
            $table->foreignUlid('client_id')
                ->nullable()
                ->constrained('client')
                ->onDelete('cascade');
        });
    }
};
