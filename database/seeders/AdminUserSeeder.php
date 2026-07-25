<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
     use WithoutModelEvents;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find super admin role
        $AdminRole = Role::firstOrCreate(['name' => 'superadmin']);

        // Create super admin user and assign role
        $AdminUser = User::firstOrCreate([
            'name' => 'Super Admin',
            'email' => 'aftab.traders@gmail.com',
            'user_type' => 'superadmin',
            'role_id' => $AdminRole->id,
            'password' => Hash::make('aftab@123'),
        ]);
        $AdminUser->assignRole($AdminRole);
    }
}
