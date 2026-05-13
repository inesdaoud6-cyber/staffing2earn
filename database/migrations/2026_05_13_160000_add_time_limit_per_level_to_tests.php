<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tests', function (Blueprint $table) {
            if (! Schema::hasColumn('tests', 'time_limit_per_level')) {
                $table->integer('time_limit_per_level')
                    ->nullable()
                    ->after('talent_threshold')
                    ->comment('Time limit in seconds for each level (null = no limit)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tests', function (Blueprint $table) {
            if (Schema::hasColumn('tests', 'time_limit_per_level')) {
                $table->dropColumn('time_limit_per_level');
            }
        });
    }
};
