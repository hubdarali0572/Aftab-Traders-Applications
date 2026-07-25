<?php

namespace Database\Seeders;

use Database\Seeders\AdminUserSeeder;
use Database\Seeders\NormalUserSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);
        $this->call(AdminUserSeeder::class);
        $this->call(NormalUserSeeder::class);
    }
}
