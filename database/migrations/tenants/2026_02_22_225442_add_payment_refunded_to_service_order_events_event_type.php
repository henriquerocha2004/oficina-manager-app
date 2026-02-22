<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::connection(config('database.connections_names.tenant'))->statement(
            "ALTER TABLE service_order_events DROP CONSTRAINT service_order_events_event_type_check"
        );

        DB::connection(config('database.connections_names.tenant'))->statement(
            "ALTER TABLE service_order_events ADD CONSTRAINT service_order_events_event_type_check 
            CHECK (event_type IN ('status_changed', 'item_added', 'item_removed', 'diagnosis_updated', 'payment_received', 'payment_refunded', 'note_added'))"
        );
    }

    public function down(): void
    {
        DB::connection(config('database.connections_names.tenant'))->statement(
            "ALTER TABLE service_order_events DROP CONSTRAINT service_order_events_event_type_check"
        );

        DB::connection(config('database.connections_names.tenant'))->statement(
            "ALTER TABLE service_order_events ADD CONSTRAINT service_order_events_event_type_check 
            CHECK (event_type IN ('status_changed', 'item_added', 'item_removed', 'diagnosis_updated', 'payment_received', 'note_added'))"
        );
    }
};
