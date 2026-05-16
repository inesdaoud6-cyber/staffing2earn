<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidate_notifications', function (Blueprint $table) {
            if (! Schema::hasColumn('candidate_notifications', 'application_progress_id')) {
                $table->foreignId('application_progress_id')
                    ->nullable()
                    ->after('offre_id')
                    ->constrained('application_progress')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('candidate_notifications', function (Blueprint $table) {
            if (Schema::hasColumn('candidate_notifications', 'application_progress_id')) {
                $table->dropConstrainedForeignId('application_progress_id');
            }
        });
    }
};
