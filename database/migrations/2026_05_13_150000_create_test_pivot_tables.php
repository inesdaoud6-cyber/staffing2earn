<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('question_test')) {
            Schema::create('question_test', function (Blueprint $table) {
                $table->id();
                $table->foreignId('question_id')
                    ->constrained('questions')
                    ->cascadeOnDelete();
                $table->foreignId('test_id')
                    ->constrained('tests')
                    ->cascadeOnDelete();

                $table->unique(['question_id', 'test_id']);
                $table->index('test_id');
            });
        }

        if (! Schema::hasTable('test_block')) {
            Schema::create('test_block', function (Blueprint $table) {
                $table->id();
                $table->foreignId('test_id')
                    ->constrained('tests')
                    ->cascadeOnDelete();
                $table->foreignId('block_id')
                    ->constrained('blocks')
                    ->cascadeOnDelete();

                $table->unique(['test_id', 'block_id']);
                $table->index('block_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('question_test');
        Schema::dropIfExists('test_block');
    }
};
