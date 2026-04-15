<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant', function (Blueprint $table) {
            $table->string('status')->default('active')->after('is_active');
            $table->timestamp('trial_until')->nullable()->after('status');
            $table->string('client_id', 26)->nullable()->after('trial_until');
            $table->foreign('client_id')->references('id')->on('clients');
        });
    }

    public function down(): void
    {
        Schema::table('tenant', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn(['status', 'trial_until', 'client_id']);
        });
    }
};
