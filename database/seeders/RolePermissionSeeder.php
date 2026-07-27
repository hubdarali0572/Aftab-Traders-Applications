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

            // for WareHouse
            'view WareHouse',
            'create WareHouse',
            'edit WareHouse',
            'show WareHouse',
            'delete WareHouse',

            // for WareHouse Stock
            'view WareHouse Stock',
            'create WareHouse Stock',
            'edit WareHouse Stock',
            'show WareHouse Stock',
            'delete WareHouse Stock',

            // for Stock Ledgers
            'view Stock Ledgers',
            'create Stock Ledgers',
            'edit Stock Ledgers',
            'show Stock Ledgers',
            'delete Stock Ledgers',

            // for Opening Stocks
            'view Opening Stocks',
            'create Opening Stocks',
            'edit Opening Stocks',
            'show Opening Stocks',
            'delete Opening Stocks',

            // for Opening Stocks Detail
            'view Opening Stocks Detail',
            'create Opening Stocks Detail',
            'edit Opening Stocks Detail',
            'show Opening Stocks Detail',
            'delete Opening Stocks Detail',

            // for Stocks Adjustments
            'view Stocks Adjustments',
            'create Stocks Adjustments',
            'edit Stocks Adjustments',
            'show Stocks Adjustments',
            'delete Stocks Adjustments',

            // for Stocks Adjustments Detail
            'view Stocks Adjustments Detail',
            'create Stocks Adjustments Detail',
            'edit Stocks Adjustments Detail',
            'show Stocks Adjustments Detail',
            'delete Stocks Adjustments Detail',

            // for Stocks Transfer
            'view Stocks Transfer',
            'create Stocks Transfer',
            'edit Stocks Transfer',
            'show Stocks Transfer',
            'delete Stocks Transfer',

            // for Stocks Transfers
            'view Stocks Transfers',
            'create Stocks Transfers',
            'edit Stocks Transfers',
            'show Stocks Transfers',
            'delete Stocks Transfers',

            // for Stocks Transfer Details
            'view Stocks Transfer Details',
            'create Stocks Transfer Details',
            'edit Stocks Transfer Details',
            'show Stocks Transfer Details',
            'delete Stocks Transfer Details',

            // for Damaged Stocks
            'view Damaged Stocks',
            'create Damaged Stocks',
            'edit Damaged Stocks',
            'show Damaged Stocks',
            'delete Damaged Stocks',

            // for Damaged Stock Details
            'view Damaged Stock Details',
            'create Damaged Stock Details',
            'edit Damaged Stock Details',
            'show Damaged Stock Details',
            'delete Damaged Stock Details',


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
