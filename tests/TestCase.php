<?php

namespace Tests;

use App\Enums\UserEnum;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles before running seeders
        $this->createRoles();

        // Run seeders in specific order
        $this->seed('DatabaseSeeder');

        // Configure services for testing
        config([
            'services.user_management.base_url' => env('SERVICES_USER_MANAGEMENT_BASE_URL', 'http://localhost:8000'),
            'services.user_management.key' => env('SERVICES_USER_MANAGEMENT_KEY', 'test-key'),
            'services.user_management.id' => env('SERVICES_USER_MANAGEMENT_ID', 'test-id'),
        ]);

        config(['auth.guards.api.driver' => 'session']);
        config(['auth.defaults.guard' => 'web']);
        config(['permission.register_permission_check_method' => true]);
    }

    protected function getTestUserHeaders($user = null)
    {
        if (!$user) {
            $user = User::factory()->create();
        }

        $token = $user->createToken('test-token')->plainTextToken;

        return [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ];
    }

    /**
     * Create basic roles for testing
     */
    protected function createRoles(): void
    {
        // Create roles if they don't exist
        if (!app(\Spatie\Permission\Models\Role::class)->where('name', 'User')->exists()) {
            app(\Spatie\Permission\Models\Role::class)->create(['name' => 'User']);
            app(\Spatie\Permission\Models\Role::class)->create(['name' => 'Admin']);
            app(\Spatie\Permission\Models\Role::class)->create(['name' => 'Super Admin']);
            app(\Spatie\Permission\Models\Role::class)->create(['name' => 'Moderator']);
        }
    }
}
