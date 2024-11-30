<?php

namespace Tests\Feature;

use App\Models\CompanyMember;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use App\Enums\CompanyMemberEnum;
use App\Enums\UserEnum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CompanyMemberControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $companies;
    protected $headers;
    protected $user;
    protected $bearerToken = 'test-token';
    protected array $companyIds;

    protected function setUp(): void
    {
        parent::setUp();

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
        // Create test user
        $this->user = User::factory()->create();

        //assign role to user
        $this->user->assignRole(UserEnum::getSuperAdmin());
        $this->user->role = UserEnum::getSuperAdmin();
        $this->user->save();

        // Mock HTTP client for auth verification
        // Create test user with admin role
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'name' => 'Test User'
        ]);
        $this->user->assignRole(UserEnum::getSuperAdmin());
        $this->user->role = UserEnum::getSuperAdmin();
        $this->user->save();

        // Set default headers
        $this->headers = [
            'Authorization' => 'Bearer ' . $this->bearerToken,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        // Mock HTTP client for auth verification
        Http::fake([
            config('services.user_management.base_url') . '/api/auth/verify-token' => function ($request) {
                if (
                    $request->hasHeader('Authorization') &&
                    $request->header('Authorization')[0] === 'Bearer ' . $this->bearerToken
                ) {
                    return Http::response([
                        'id' => $this->user->id,
                        'name' => $this->user->name,
                        'email' => $this->user->email,
                        'status' => 'active',
                        'status' => 'active',
                        'roles' => [UserEnum::getSuperAdmin()],
                        'permissions' => [
                            'create company',
                            'update company',
                            'delete company',
                            'view company',
                            'create companygroup',
                            'update companygroup',
                            'delete companygroup',
                            'view companygroup'
                        ]
                    ], 200);
                }
                return Http::response(['error' => 'Unauthorized'], 401);
            }
        ]);

        // Create test data
        $this->companies = Company::factory()->count(5)->create();
        $this->companyIds = $this->companies->pluck('id')->toArray();
    }

    public function test_index_returns_company_members_with_authentication()
    {
        // Create test company members
        CompanyMember::factory()->count(3)->create([
            'company_id' => $this->companyIds[array_rand($this->companyIds)]
        ]);

        $response = $this->withHeaders($this->headers)
            ->getJson('/api/company-members');

        $response->assertStatus(200);
    }

    public function test_store_creates_company_member_with_authentication()
    {
        $user = User::factory()->create();

        $payload = [
            'user_id' => $user->id,
            'email' => $user->email,
            'company_id' => $this->companyIds[array_rand($this->companyIds)],
            'role' => CompanyMemberEnum::getMemberRole(),
        ];

        $response = $this->withHeaders($this->headers)
            ->postJson('/api/company-members', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('company_members', [
            'email' => $user->email,
            'user_id' => $user->id
        ]);
    }

    public function test_show_returns_company_member_with_authentication()
    {
        $member = CompanyMember::factory()->create([
            'company_id' => $this->companyIds[array_rand($this->companyIds)]
        ]);

        $response = $this->withHeaders($this->headers)
            ->getJson("/api/company-members/{$member->id}");

        $response->assertStatus(200);
    }

    public function test_update_modifies_company_member_with_authentication()
    {
        $member = CompanyMember::factory()->create([
            'company_id' => $this->companyIds[array_rand($this->companyIds)]
        ]);

        $user = User::factory()->create();
        $payload = [
            'user_id' => $user->id,
            'email' => $user->email,
            'company_id' => $this->companyIds[array_rand($this->companyIds)],
            'role' => CompanyMemberEnum::getMemberRole(),
        ];

        $response = $this->withHeaders($this->headers)
            ->putJson("/api/company-members/{$member->id}", $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('company_members', [
            'id' => $member->id,
            'email' => $user->email
        ]);
    }

    public function test_destroy_deletes_company_member_with_authentication()
    {
        $member = CompanyMember::factory()->create([
            'company_id' => $this->companyIds[array_rand($this->companyIds)]
        ]);

        $response = $this->withHeaders($this->headers)
            ->deleteJson("/api/company-members/{$member->id}");

        $response->assertStatus(204);

        $result = CompanyMember::find($member->id);
        $this->assertNull($result);
    }

    public function test_unauthorized_access_is_rejected()
    {
        $response = $this->getJson('/api/company-members');
        $response->assertStatus(401)
            ->assertJson(['error' => 'Unauthorized: No token provided']);
    }

    public function test_invalid_token_is_rejected()
    {
        $response = $this->withHeaders([
            'Authorization' => 'invalid-token',
            'Accept' => 'application/json'
        ])->getJson('/api/company-members');

        $response->assertStatus(401)
            ->assertJson(['error' => 'Unauthorized: No token provided']);
    }

    public function test_missing_bearer_token_is_rejected()
    {
        $response = $this->withHeaders([
            'Authorization' => $this->bearerToken,
            'Accept' => 'application/json'
        ])->getJson('/api/company-members');

        $response->assertStatus(401)
            ->assertJson(['error' => 'Unauthorized: No token provided']);
    }
}
