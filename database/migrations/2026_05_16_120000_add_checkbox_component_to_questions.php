<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE questions MODIFY component ENUM('radio', 'list', 'checkbox', 'text', 'date', 'photo') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE questions MODIFY component ENUM('radio', 'list', 'text', 'date', 'photo') NOT NULL");
    }
};
