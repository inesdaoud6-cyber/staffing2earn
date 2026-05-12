<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
      public function up(): void
      {
            Schema::create('responses', function (Blueprint $table) {
                  $table->id();
                  $table->foreignId('application_id')->constrained('application_progress')->onDelete('cascade');
                  $table->integer('level')->default(1);
                  $table->timestamps();
                  $table->index(['application_id', 'level']);
            });
      }

      public function down(): void
      {
            Schema::dropIfExists('responses');
      }
};
