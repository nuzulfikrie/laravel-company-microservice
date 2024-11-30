<?php

// database/seeders/UsersSeeder.php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Enums\UserEnum;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create a Super Admin
        $superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
        ]);
        $superAdmin->assignRole(UserEnum::getSuperAdmin());

        // Create a Moderator
        $moderator = User::factory()->create([
            'name' => 'Moderator',
            'email' => 'moderator@example.com',
        ]);
        $moderator->assignRole(UserEnum::getModerator());

        // Create an Admin
        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
        ]);
        $admin->assignRole(UserEnum::getAdmin());

        // Create some regular users
        User::factory(10)->create()->each(function ($user) {
            $user->assignRole(UserEnum::getUser());
        });
    }
}