<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
      public function up(): void
      {
            Schema::create('response_notes', function (Blueprint $table) {
                  $table->id();
                  $table->foreignId('question_response_id')->constrained('question_responses')->onDelete('cascade');
                  $table->foreignId('reviewer_id')->nullable()->constrained('users')->onDelete('set null');
                  $table->text('note');
                  $table->timestamps();
            });
      }

      public function down(): void
      {
            Schema::dropIfExists('response_notes');
      }
};
