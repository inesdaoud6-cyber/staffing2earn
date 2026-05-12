<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view-candidate-scores',
            'edit-candidate-status',
            'view-all-applications',
            'send-candidate-notification',
            'download-candidate-cv',
            'view-test-results-detail',
            'manage-candidates',
            'manage-offres',
            'manage-tests',
            'manage-questions',
            'manage-users',
            'view-stats',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        Role::firstOrCreate(['name' => 'candidate']);

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions($permissions);
    }
}