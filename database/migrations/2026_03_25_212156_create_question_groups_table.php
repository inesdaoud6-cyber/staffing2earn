<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
      public function up(): void
      {
            Schema::create('question_groups', function (Blueprint $table) {
                  $table->id();
                  $table->foreignId('block_id')->constrained('blocks')->onDelete('cascade');
                  $table->string('name');
                  $table->integer('order')->default(0);
                  $table->timestamps();
                  $table->index('block_id');
            });
      }

      public function down(): void
      {
            Schema::dropIfExists('question_groups');
      }
};
