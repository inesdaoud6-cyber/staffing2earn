<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offres', function (Blueprint $table) {
            if (! Schema::hasColumn('offres', 'title')) {
                $table->string('title')->after('id');
            }
            if (! Schema::hasColumn('offres', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
            if (! Schema::hasColumn('offres', 'domain')) {
                $table->string('domain')->nullable()->after('description');
            }
            if (! Schema::hasColumn('offres', 'location')) {
                $table->string('location')->nullable()->after('domain');
            }
            if (! Schema::hasColumn('offres', 'contract_type')) {
                $table->string('contract_type')->nullable()->after('location');
            }
            if (! Schema::hasColumn('offres', 'deadline')) {
                $table->date('deadline')->nullable()->after('contract_type');
            }
            if (! Schema::hasColumn('offres', 'is_published')) {
                $table->boolean('is_published')->default(false)->after('deadline');
            }
            if (! Schema::hasColumn('offres', 'test_id')) {
                $table->foreignId('test_id')
                    ->nullable()
                    ->after('is_published')
                    ->constrained('tests')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('offres', function (Blueprint $table) {
            if (Schema::hasColumn('offres', 'test_id')) {
                $table->dropConstrainedForeignId('test_id');
            }
            $table->dropColumn([
                'title',
                'description',
                'domain',
                'location',
                'contract_type',
                'deadline',
                'is_published',
            ]);
        });
    }
};
