<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Enums\UserEnum;

class RoleAndPermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Existing permissions
            'create report',
            'update report',
            'delete report',
            'publish report',
            'unpublish report',
            'view report',
            'download report',
            'update user',
            'delete user',
            'create user',
            'view user',
            'create company',
            'update company',
            'delete company',
            'activate company',
            'deactivate company',
            'view company',
            'create subscription plan',
            'update subscription plan',
            'delete subscription plan',
            'view subscription plan',
            'activate subscription plan',
            'deactivate subscription plan',
            'subscribe company',
            'unsubscribe company',
            'subscribe to plan',
            'unsubscribe from plan',
            'create companygroup',
            'update companygroup',
            'delete companygroup',
            'view companygroup',
            'create payment',
            'update payment',
            'delete payment',
            'view payment',
            'create invoice',
            'update invoice',
            'delete invoice',
            'view invoice',
            'create file',
            'update file',
            'delete file',
            'view file',
            // New permissions for user management
            'view users',
            'create users',
            'edit users',
            'delete users',
            'ban users',
            'unban users',

            'create product',
            'update product',
            'delete product',
            'view product',
            'deactivate product',
            'activate product',
            'update stock',
            'view subscribable products'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Define role permissions
        $rolePermissions = [
            UserEnum::getSuperAdmin() => $permissions, // Super Admin gets all permissions

            UserEnum::getAdmin() => [
                // Existing Admin permissions
                'create companygroup',
                'update companygroup',
                'delete companygroup',
                'view companygroup',
                'subscribe company',
                'unsubscribe company',
                'subscribe to plan',
                'unsubscribe from plan',
                'create file',
                'update file',
                'delete file',
                'view file',
                'create payment',
                'update payment',
                'view payment',
                // New Admin permissions
                'view users',
                'create users',
                'edit users',
                'ban users',
                'unban users',
                'create product',
                'update product',
                'delete product',
                'view product',
                'deactivate product',
                'activate product',
                'update stock',
                'view subscribable products'
            ],

            UserEnum::getModerator() => [
                // Existing Moderator permissions
                'create subscription plan',
                'update subscription plan',
                'delete subscription plan',
                'view subscription plan',
                'activate subscription plan',
                'deactivate subscription plan',
                'subscribe company',
                'unsubscribe company',
                'subscribe to plan',
                'unsubscribe from plan',
                // New Moderator permissions
                'view users',
                'ban users',
                'unban users',
            ],

            UserEnum::getUser() => [
                // Existing User permissions
                'update user',
                'view user',
                'view company',
                'view companygroup',
                'create file',
                'update file',
                'delete file',
                'view file',
                'view payment',
                'view invoice',
                'create report',
                'view report',
                'download report',
                'update report',
                'subscribe to plan',
                'unsubscribe from plan',
                // New User permissions
                'view users',
            ],
        ];

        // Create roles and assign permissions
        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($permissions);
        }

        // Create default users if they don't exist
        if (!\App\Models\User::where('email', 'superadmin@test.com')->exists()) {
            $user = \App\Models\User::factory()->create([
                'email' => 'superadmin@test.com',
                'name' => UserEnum::getSuperAdmin()
            ]);
            $user->assignRole(UserEnum::getSuperAdmin());
            $user->role = UserEnum::getSuperAdmin();
            $user->save();
        }

        if (!\App\Models\User::where('email', 'admin@test.com')->exists()) {
            $user = \App\Models\User::factory()->create([
                'email' => 'admin@test.com',
                'name' => UserEnum::getAdmin()
            ]);
            $user->assignRole(UserEnum::getAdmin());
            $user->role = UserEnum::getAdmin();
            $user->save();
        }

        if (!\App\Models\User::where('email', 'moderator@test.com')->exists()) {
            $user = \App\Models\User::factory()->create([
                'email' => 'moderator@test.com',
                'name' => UserEnum::getModerator()
            ]);
            $user->assignRole(UserEnum::getModerator());

            $user->role = UserEnum::getModerator();
            $user->save();
        }

        // Create some regular users
        \App\Models\User::factory()
            ->count(50)
            ->create()
            ->each(function ($user) {
                $user->assignRole(UserEnum::getUser());
                $user->role = UserEnum::getUser();
                $user->save();
            });
    }
}
