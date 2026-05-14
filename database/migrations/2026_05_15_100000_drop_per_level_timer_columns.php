<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tests', function (Blueprint $table) {
            if (Schema::hasColumn('tests', 'time_limit_per_level')) {
                $table->dropColumn('time_limit_per_level');
            }
        });

        Schema::table('application_progress', function (Blueprint $table) {
            if (Schema::hasColumn('application_progress', 'level_started_at')) {
                $table->dropColumn('level_started_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tests', function (Blueprint $table) {
            if (! Schema::hasColumn('tests', 'time_limit_per_level')) {
                $table->integer('time_limit_per_level')->nullable();
            }
        });

        Schema::table('application_progress', function (Blueprint $table) {
            if (! Schema::hasColumn('application_progress', 'level_started_at')) {
                $table->timestamp('level_started_at')->nullable();
            }
        });
    }
};
