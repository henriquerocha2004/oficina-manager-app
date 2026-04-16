<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        DB::statement('ALTER TABLE sessions ALTER COLUMN user_id TYPE VARCHAR(255) USING user_id::varchar');
    }

    public function down(): void
    {
        DB::statement("UPDATE sessions SET user_id = NULL WHERE user_id IS NOT NULL AND user_id !~ '^[0-9]+$'");
        DB::statement('ALTER TABLE sessions ALTER COLUMN user_id TYPE BIGINT USING user_id::bigint');
    }
};
