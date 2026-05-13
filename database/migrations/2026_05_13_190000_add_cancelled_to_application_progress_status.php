<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Extend the ENUM in place. Doctrine/DBAL doesn't support ENUM changes,
        // so we issue raw SQL. The default stays 'pending'.
        DB::statement(
            "ALTER TABLE `application_progress` "
            . "MODIFY COLUMN `status` ENUM('pending','in_progress','validated','rejected','cancelled') "
            . "NOT NULL DEFAULT 'pending'"
        );
    }

    public function down(): void
    {
        // Anything currently in 'cancelled' would block the rollback otherwise.
        DB::statement("UPDATE `application_progress` SET `status` = 'rejected' WHERE `status` = 'cancelled'");
        DB::statement(
            "ALTER TABLE `application_progress` "
            . "MODIFY COLUMN `status` ENUM('pending','in_progress','validated','rejected') "
            . "NOT NULL DEFAULT 'pending'"
        );
    }
};
