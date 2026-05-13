<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('application_progress', function (Blueprint $table) {
            if (! Schema::hasColumn('application_progress', 'cv_path')) {
                $table->string('cv_path')
                    ->nullable()
                    ->after('test_id')
                    ->comment('CV file chosen for this specific application (null = use candidate.cv_path)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('application_progress', function (Blueprint $table) {
            if (Schema::hasColumn('application_progress', 'cv_path')) {
                $table->dropColumn('cv_path');
            }
        });
    }
};
