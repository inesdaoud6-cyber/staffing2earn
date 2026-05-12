<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
      public function up(): void
      {
            Schema::create('question_responses', function (Blueprint $table) {
                  $table->id();
                  $table->foreignId('response_id')->constrained('responses')->onDelete('cascade');
                  $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
                  $table->foreignId('answer_id')->nullable()->constrained('answers')->onDelete('set null');
                  $table->float('auto_score')->default(0);
                  $table->float('manual_score')->default(0);
                  $table->float('obtained_score')->default(0);
                  $table->text('text_answer')->nullable();
                  $table->timestamps();
                  $table->index(['response_id', 'question_id']);
            });
      }

      public function down(): void
      {
            Schema::dropIfExists('question_responses');
      }
};
