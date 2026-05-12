<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidate_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type')->default('info');
            $table->string('title');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->foreignId('offre_id')->nullable()->constrained('offres')->onDelete('set null');
            $table->timestamps();

            $table->index('user_id');
            $table->index('is_read');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_notifications');
    }
};