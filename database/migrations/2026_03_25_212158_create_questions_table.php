<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('block_id')->nullable()->constrained('blocks')->onDelete('cascade');
            $table->foreignId('group_id')->nullable()->constrained('question_groups')->onDelete('cascade');
            $table->foreignId('offre_id')->nullable()->constrained('offres')->onDelete('set null');
            $table->text('question_fr');
            $table->text('question_en')->nullable();
            $table->text('question_ar')->nullable();
            $table->enum('component', ['radio', 'list', 'text', 'date', 'photo']);
            $table->integer('level')->default(1);
            $table->boolean('obligatory')->default(false);
            $table->boolean('scorable')->default(true);
            $table->boolean('auto_evaluation')->default(false);
            $table->string('correct_answer')->nullable();
            $table->enum('classification', ['primary', 'secondary'])->default('primary');
            $table->float('max_note')->default(0);
            $table->float('second_ratio')->default(0);
            $table->text('user_note')->nullable();
            $table->text('note_rule')->nullable();
            $table->json('possible_answers')->nullable();
            $table->timestamps();

            $table->index('level');
            $table->index(['block_id', 'group_id']);
            $table->index('offre_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};