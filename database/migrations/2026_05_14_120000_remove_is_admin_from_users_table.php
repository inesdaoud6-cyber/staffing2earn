<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'is_admin')) {
            return;
        }

        foreach (['admin', 'candidate'] as $name) {
            Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        foreach (User::query()->cursor() as $user) {
            if ($user->roles()->exists()) {
                continue;
            }

            $isAdmin = (bool) ($user->getRawOriginal('is_admin') ?? false);
            $user->assignRole($isAdmin ? 'admin' : 'candidate');
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('is_admin');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('is_admin')->default(false)->after('password');
        });

        User::query()->each(function (User $user): void {
            $user->forceFill([
                'is_admin' => $user->hasRole('admin'),
            ])->saveQuietly();
        });
    }
};
