<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\Company;
use App\Enums\UserEnum;
use App\Enums\CompanyEnum;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;

class CompanyControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $headers;
    protected $user;
    protected $bearerToken = 'test-token';

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
        Http::fake([
            config('services.user_management.base_url') . '/api/auth/verify-token' => function ($request) {
                // Check if the request has proper authorization header
                if (
                    $request->hasHeader('Authorization') &&
                    $request->header('Authorization')[0] === 'Bearer ' . $this->bearerToken
                ) {
                    return Http::response([
                        'id' => $this->user->id,
                        'name' => $this->user->name,
                        'email' => $this->user->email,
                        'status' => 'active',
                        'roles' => [UserEnum::getSuperAdmin()],
                        'permissions' => ['create company', 'update company', 'delete company', 'view company']
                    ], 200);
                }
                return Http::response(['error' => 'Unauthorized'], 401);
            }
        ]);

        $this->headers = [
            'Authorization' => 'Bearer ' . $this->bearerToken,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    #[Test]
    public function index_returns_list_of_companies()
    {
        // Create test companies
        Company::factory(3)->create([
            'original_admin_id' => $this->user->id,
            'status' =>  CompanyEnum::getActive(),
        ]);

        $response = $this->withHeaders($this->headers)
            ->getJson('/api/companies');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'status',
                        'has_multiple_subscriptions',
                        'original_admin_id',
                        'created_at',
                        'updated_at',
                        'deleted_at'
                    ]
                ]
            ]);
    }

    #[Test]
    public function show_returns_company_details()
    {
        $company = Company::factory()->create([
            'original_admin_id' => $this->user->id,
            'status' =>  CompanyEnum::getActive(),
        ]);

        $response = $this->withHeaders($this->headers)
            ->getJson("/api/companies/{$company->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'status',
                    'has_multiple_subscriptions',
                    'original_admin_id',
                    'created_at',
                    'updated_at',
                    'deleted_at'
                ]
            ]);
    }

    #[Test]
    public function store_creates_new_company()
    {
        $companyData = [
            'name' => 'New Test Company',
            'email' => 'test@newcompany.com',
            'status' =>  CompanyEnum::getActive(),
            'has_multiple_subscriptions' => false,
            'original_admin_id' => $this->user->id
        ];

        $response = $this->withHeaders($this->headers)
            ->postJson('/api/companies', $companyData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'status',
                    'has_multiple_subscriptions',
                    'original_admin_id',
                    'created_at',
                    'updated_at'
                ]
            ])
            ->assertJsonFragment([
                'name' => $companyData['name'],
                'email' => $companyData['email'],
                'status' => $companyData['status'],
                'has_multiple_subscriptions' => $companyData['has_multiple_subscriptions']
            ]);

        $this->assertDatabaseHas('companies', [
            'name' => $companyData['name'],
            'email' => $companyData['email'],
            'original_admin_id' => $this->user->id,
            'status' => $companyData['status'],
            'has_multiple_subscriptions' => $companyData['has_multiple_subscriptions'],
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    #[Test]
    public function update_modifies_existing_company()
    {
        $company = Company::factory()->create([
            'original_admin_id' => $this->user->id,
            'status' =>  CompanyEnum::getActive(),
        ]);

        $updateData = [
            'name' => 'Updated Company Name',
            'email' => 'updated@company.com',
            'status' =>  CompanyEnum::getActive(),
            'has_multiple_subscriptions' => true,
            'original_admin_id' => $this->user->id
        ];

        $response = $this->withHeaders($this->headers)
            ->putJson("/api/companies/{$company->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'status',
                    'has_multiple_subscriptions',
                    'original_admin_id',
                    'created_at',
                    'updated_at'
                ]
            ])
            ->assertJsonFragment([
                'name' => $updateData['name'],
                'email' => $updateData['email']
            ]);
    }

    #[Test]
    public function destroy_deletes_company()
    {
        $company = Company::factory()->create([
            'original_admin_id' => $this->user->id,
            'status' =>  CompanyEnum::getActive(),
        ]);

        $response = $this->withHeaders($this->headers)
            ->deleteJson("/api/companies/{$company->id}");

        $response->assertStatus(204);
        $this->assertSoftDeleted('companies', ['id' => $company->id]);
    }

    #[Test]
    public function store_validates_required_fields()
    {
        $response = $this->withHeaders($this->headers)
            ->postJson('/api/companies', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'email',
                'status',
                'has_multiple_subscriptions',
                'original_admin_id'
            ]);
    }

    #[Test]
    public function show_returns_404_for_non_existent_company()
    {
        $response = $this->withHeaders($this->headers)
            ->getJson('/api/companies/99999');

        $response->assertStatus(404);
    }

    #[Test]
    public function unauthorized_requests_are_rejected()
    {
        $response = $this->getJson('/api/companies');

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Unauthorized: No token provided'
            ]);
    }

    #[Test]
    public function invalid_token_requests_are_rejected()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token',
            'Accept' => 'application/json',
        ])->getJson('/api/companies');

        $response->assertStatus(401);
    }

    #[Test]
    public function non_admin_users_cannot_create_company()
    {
        $regularUser = User::factory()->create([
            'email' => 'regular@example.com',
            'status' => UserEnum::Active
        ]);
        $regularUser->assignRole(UserEnum::getUser());

        // Define user token
        $userToken = 'regular-user-token';

        // Update mock to return 200 with user data
        Http::fake([
            config('services.user_management.base_url') . '/api/auth/verify-token' => Http::response([
                'id' => $regularUser->id,
                'name' => $regularUser->name,
                'email' => $regularUser->email,
                'roles' => [UserEnum::getUser()],
                'permissions' => ['view company']
            ], 200)
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$userToken}",
            'Accept' => 'application/json',
        ])->postJson('/api/companies', [
            'name' => 'Test Company',
            'email' => 'test@company.com',
            'status' => CompanyEnum::getActive(),
            'has_multiple_subscriptions' => false,
            'original_admin_id' => $regularUser->id
        ]);

        $response->assertStatus(401)
            ->assertJson(['error' => 'Unauthorized: Invalid token']);
    }
}
