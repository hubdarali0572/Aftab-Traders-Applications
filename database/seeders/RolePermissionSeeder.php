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

            // for Brand
            'view Brand',
            'create Brand',
            'edit Brand',
            'show Brand',
            'delete Brand',

            // for Product Category
            'view Product Category',
            'create Product Category',
            'edit Product Category',
            'show Product Category',
            'delete Product Category',

            // for Product Unit
            'view Product Unit',
            'create Product Unit',
            'edit Product Unit',
            'show Product Unit',
            'delete Product Unit',

            // for Product Main
            'view Product Main',
            'create Product Main',
            'edit Product Main',
            'show Product Main',
            'delete Product Main',

            // for Selling Price
            'view Selling Price',
            'create Selling Price',
            'edit Selling Price',
            'show Selling Price',
            'delete Selling Price',

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
