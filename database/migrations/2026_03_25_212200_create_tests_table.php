<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
      public function up(): void
      {
            Schema::create('tests', function (Blueprint $table) {
                  $table->id();
                  $table->string('name');
                  $table->text('description')->nullable();
                  $table->float('eligibility_threshold')->default(50);
                  $table->float('talent_threshold')->default(80);
                  $table->timestamps();
                  $table->index('name');
            });
      }

      public function down(): void
      {
            Schema::dropIfExists('tests');
      }
};
