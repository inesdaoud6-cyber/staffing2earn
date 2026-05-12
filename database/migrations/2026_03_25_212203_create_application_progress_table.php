<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('candidates')->onDelete('cascade');
            $table->foreignId('offre_id')->nullable()->constrained('offres')->onDelete('set null');
            $table->foreignId('test_id')->nullable()->constrained('tests')->onDelete('set null');
            $table->enum('status', ['pending', 'in_progress', 'validated', 'rejected'])->default('pending');
            $table->integer('current_level')->default(1);
            $table->float('main_score')->default(0);
            $table->float('secondary_score')->default(0);
            $table->boolean('apply_enabled')->default(false);
            $table->boolean('score_published')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->timestamps();

            $table->index('candidate_id');
            $table->index('offre_id');
            $table->index('status');
            $table->index('is_archived');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_progress');
    }
};