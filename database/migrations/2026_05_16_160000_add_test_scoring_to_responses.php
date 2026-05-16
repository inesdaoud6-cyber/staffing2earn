<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('responses', function (Blueprint $table) {
            $table->decimal('test_score', 5, 2)->nullable()->after('level');
            $table->boolean('eligibility_passed')->default(false)->after('test_score');
            $table->boolean('talent_passed')->default(false)->after('eligibility_passed');
        });
    }

    public function down(): void
    {
        Schema::table('responses', function (Blueprint $table) {
            $table->dropColumn(['test_score', 'eligibility_passed', 'talent_passed']);
        });
    }
};
