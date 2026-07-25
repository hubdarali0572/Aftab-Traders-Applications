<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    use WithoutModelEvents;
    /**
     * Run the database seeds.
     */
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $this->seedPermissions();
        $this->seedRoles();
    }

    private function seedPermissions()
    {
        $permissions = [

            // for user
            'view user',
            'create user',
            'edit user',
            'show user',
            'delete user',

            // for Roles
            'view role',
            'create role',
            'edit role',
            'show role',
            'delete role',


        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }

    private function seedRoles()
    {

        // Create roles
        $superAdminRole = Role::firstOrCreate(['name' => 'SuperAdmin']);
        // Assign permissions to roles
        $superAdminRole->givePermissionTo(Permission::all());
    }
}
