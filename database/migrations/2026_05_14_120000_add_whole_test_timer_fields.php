<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tests', function (Blueprint $table) {
            if (! Schema::hasColumn('tests', 'whole_test_timer_enabled')) {
                $col = $table->boolean('whole_test_timer_enabled')->default(false);
                if (Schema::hasColumn('tests', 'time_limit_per_level')) {
                    $col->after('time_limit_per_level');
                }
            }
            if (! Schema::hasColumn('tests', 'whole_test_timer_minutes')) {
                $col = $table->unsignedInteger('whole_test_timer_minutes')->nullable();
                if (Schema::hasColumn('tests', 'whole_test_timer_enabled')) {
                    $col->after('whole_test_timer_enabled');
                }
            }
        });

        Schema::table('application_progress', function (Blueprint $table) {
            if (! Schema::hasColumn('application_progress', 'test_session_expires_at')) {
                $col = $table->timestamp('test_session_expires_at')->nullable();
                if (Schema::hasColumn('application_progress', 'level_started_at')) {
                    $col->after('level_started_at');
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('tests', function (Blueprint $table) {
            if (Schema::hasColumn('tests', 'whole_test_timer_minutes')) {
                $table->dropColumn('whole_test_timer_minutes');
            }
            if (Schema::hasColumn('tests', 'whole_test_timer_enabled')) {
                $table->dropColumn('whole_test_timer_enabled');
            }
        });

        Schema::table('application_progress', function (Blueprint $table) {
            if (Schema::hasColumn('application_progress', 'test_session_expires_at')) {
                $table->dropColumn('test_session_expires_at');
            }
        });
    }
};
