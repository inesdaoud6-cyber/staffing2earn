<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('application_progress')
            ->where('level_status', 'rejected')
            ->where('status', 'in_progress')
            ->update(['status' => 'rejected']);
    }

    public function down(): void
    {
        // Cannot reliably distinguish auto-fail from other cases; leave data as-is on rollback.
    }
};
