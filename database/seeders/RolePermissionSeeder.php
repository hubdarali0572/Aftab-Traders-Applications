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
        $modules = [
            'user',
            'role',
            'brand',
            'product category',
            'product unit',
            'product main',
            'selling price',
            'warehouse',
            'warehouse stock',
            'stock ledgers',
            'opening stocks',
            'opening stock details',
            'stock adjustments',
            'stock adjustments detail',
            'stock transfers',
            'stock transfer detail',
            'damaged stocks',
            'damaged stock details',
            'purchase',
            'purchase details',
            'purchase return',
            'purchase return details',
            'purchase expense',
            'customer',
            'customer ledgers',
            'sale',
            'sale detail',
        ];

        $actions = ['view', 'create', 'edit', 'show', 'delete'];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$action} {$module}"]);
            }
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
