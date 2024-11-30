<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\CompanySeeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    public function run()
    {

        // Run other seeders
        $this->call([
            RoleSeeder::class,
            RoleAndPermissionSeeder::class,
            UserSeeder::class,
            CompanySeeder::class,
        ]);
    }
}
