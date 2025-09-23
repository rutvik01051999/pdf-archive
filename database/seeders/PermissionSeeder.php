<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'create-update-user',
                'guard_name' => 'web',
            ],
            [
                'name' => 'delete-user',
                'guard_name' => 'web',
            ],
            [
                'name' => 'view-user',
                'guard_name' => 'web',
            ],
            [
                'name' => 'view-all-users',
                'guard_name' => 'web',
            ],
            [
                'name' => 'export-users',
                'guard_name' => 'web',
            ],
            [
                'name' => 'create-update-role',
                'guard_name' => 'web',
            ],
            [
                'name' => 'delete-role',
                'guard_name' => 'web',
            ],
            [
                'name' => 'view-role',
                'guard_name' => 'web',
            ],
            [
                'name' => 'export-roles',
                'guard_name' => 'web',
            ],
            [
                'name' => 'view-activity-logs',
                'guard_name' => 'web',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                [
                    'name' => $permission['name'],
                    'guard_name' => $permission['guard_name'],
                ],
                $permission,
            );
        }
    }
}
