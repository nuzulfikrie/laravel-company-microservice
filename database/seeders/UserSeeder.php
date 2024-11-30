<?php

namespace Database\Seeders;

use App\Enums\UserEnum;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Super Admin
        $superAdmin = User::factory()->create([
            'email' => 'superadmin@example.com',
            'name' => 'Super Admin',
        ]);
        $superAdmin->assignRole(UserEnum::getSuperAdmin());

        // Create Admin
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'name' => 'Admin User',
        ]);
        $admin->assignRole(UserEnum::getAdmin());

        // Create regular users
        User::factory(10)->create()->each(function ($user) {
            $user->assignRole(UserEnum::getUser());
        });
    }
}