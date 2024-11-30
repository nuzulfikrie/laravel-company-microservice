<?php

namespace Database\Seeders;

use App\Enums\UserEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            UserEnum::getSuperAdmin(),
            UserEnum::getAdmin(),
            UserEnum::getModerator(),
            UserEnum::getUser(),
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }
} 