<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    public function run()
    {

        // Run other seeders
        $this->call([
            CompanySeeder::class,
        ]);
    }
}
