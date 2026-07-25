<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class NormalUserSeeder extends Seeder
{
     use WithoutModelEvents;
    /**
     * Run the database seeds.
     */
     public function run(): void
    {
        // Find super admin role
        $UserRole = Role::firstOrCreate(['name' => 'user']);

        // Create super admin user and assign role
        $AdminUser = User::firstOrCreate([
            'name' => 'Normal User',
            'email' => 'user@example.com',
            'user_type' => 'user',
            'role_id' => $UserRole->id,
            'password' => Hash::make('password'),
        ]);
        $AdminUser->assignRole($UserRole);
    }
}
