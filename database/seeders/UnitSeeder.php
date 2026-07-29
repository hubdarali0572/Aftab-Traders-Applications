<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\UnitService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->orderBy('id')->first();

        if ($admin) {
            Auth::login($admin);
        }

        app(UnitService::class)->ensureDefaultUnits();
    }
}
