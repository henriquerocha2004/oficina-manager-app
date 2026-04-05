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
        Schema::table('service_order_photos', function (Blueprint $table) {
            // Rename photo_path to file_path
            $table->renameColumn('photo_path', 'file_path');

            // Add missing fields
            $table->string('mime_type')->after('file_size');
            $table->unsignedSmallInteger('width')->nullable()->after('mime_type');
            $table->unsignedSmallInteger('height')->nullable()->after('width');
            $table->unsignedTinyInteger('display_order')->default(0)->after('height');
            $table->softDeletes();

            // Add missing index
            $table->index(['service_order_id', 'display_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_order_photos', function (Blueprint $table) {
            $table->renameColumn('file_path', 'photo_path');
            $table->dropColumn(['mime_type', 'width', 'height', 'display_order', 'deleted_at']);
            $table->dropIndex(['service_order_id', 'display_order']);
        });
    }
};
