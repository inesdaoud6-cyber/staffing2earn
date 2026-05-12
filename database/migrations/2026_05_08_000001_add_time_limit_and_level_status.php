<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->integer('time_limit')->nullable()->after('level')->comment('Time limit in seconds for this level (null = no limit)');
        });

        Schema::table('application_progress', function (Blueprint $table) {
            $table->enum('level_status', ['in_progress', 'awaiting_approval', 'approved', 'rejected'])
                ->default('in_progress')
                ->after('current_level');
            $table->timestamp('level_started_at')->nullable()->after('level_status');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('time_limit');
        });

        Schema::table('application_progress', function (Blueprint $table) {
            $table->dropColumn(['level_status', 'level_started_at']);
        });
    }
};
