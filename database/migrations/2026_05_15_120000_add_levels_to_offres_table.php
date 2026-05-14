<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offres', function (Blueprint $table) {
            if (! Schema::hasColumn('offres', 'levels_count')) {
                $table->unsignedTinyInteger('levels_count')->default(2)->after('test_id');
            }
            if (! Schema::hasColumn('offres', 'level_test_ids')) {
                $table->json('level_test_ids')->nullable()->after('levels_count');
            }
        });

        if (Schema::hasColumn('offres', 'test_id') && Schema::hasColumn('offres', 'levels_count')) {
            $offres = DB::table('offres')->select('id', 'test_id')->get();
            foreach ($offres as $row) {
                $testId = $row->test_id ? (int) $row->test_id : null;
                DB::table('offres')->where('id', $row->id)->update([
                    'levels_count'   => 2,
                    'level_test_ids' => json_encode($testId ? [$testId] : []),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('offres', function (Blueprint $table) {
            if (Schema::hasColumn('offres', 'level_test_ids')) {
                $table->dropColumn('level_test_ids');
            }
            if (Schema::hasColumn('offres', 'levels_count')) {
                $table->dropColumn('levels_count');
            }
        });
    }
};
