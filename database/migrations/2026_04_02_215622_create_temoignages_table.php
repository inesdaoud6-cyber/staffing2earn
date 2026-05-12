<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('temoignages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('contenu');
            $table->integer('note')->default(5);
            $table->boolean('is_approved')->default(false);
            $table->timestamps();

            $table->index('user_id');
            $table->index('is_approved');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('temoignages');
    }
};