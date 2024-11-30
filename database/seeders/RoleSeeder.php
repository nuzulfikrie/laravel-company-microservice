<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Enums\UserEnum;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Create roles
        foreach (UserEnum::getAllRoles() as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }
} 