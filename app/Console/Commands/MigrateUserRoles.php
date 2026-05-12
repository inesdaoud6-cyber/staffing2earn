<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MigrateUserRoles extends Command
{
    protected $signature = 'roles:migrate';
    protected $description = 'Assign Spatie roles to existing users without a role';

    public function handle(): void
    {
        User::all()->each(function (User $user) {
            if ($user->getRoleNames()->isEmpty()) {
                $user->assignRole($user->is_admin ? 'admin' : 'candidate');
            }
        });

        $this->info('Roles migrated successfully.');
    }
}