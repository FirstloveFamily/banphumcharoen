<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $maxId = DB::table('services')->max('id');

        if ($maxId === null) {
            return;
        }

        DB::statement(
            "SELECT setval(pg_get_serial_sequence('services', 'id'), ?, true)",
            [$maxId]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left blank. Sequence state is derived from table data.
    }
};
