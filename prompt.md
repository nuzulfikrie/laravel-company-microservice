The attached Image is the general structure for the microservice that we wanted build.

These code below, describe  the "layer communicate with user management microservice" 


File path: config/usermanagement-auth.php

```php

<?php

return [
    'base_url' => env('USER_MANAGEMENT_URL', 'http://usermanagement-service'),
    'client_id' => env('USER_MANAGEMENT_CLIENT_ID'),
    'client_secret' => env('USER_MANAGEMENT_CLIENT_SECRET'),
    'redirect_uri' => env('USER_MANAGEMENT_REDIRECT_URI'),
];
```

File path: src/Middleware/ValidateUserManagementToken.php 
```php

<?php

namespace FintelAuth\UserManagementAuth\Middleware;

use Closure;
use FintelAuth\UserManagementAuth\UserManagementAuthClient;

class ValidateUserManagementToken
{
    private $authClient;

    public function __construct(UserManagementAuthClient $authClient)
    {
        $this->authClient = $authClient;
    }

    public function handle($request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = $this->authClient->validateToken($token);

        if (!$user) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        $request->merge(['user' => $user]);

        return $next($request);
    }
}

```

File path:src/UserManagementAuth 

File path:src/UserManagementAuth/Controllers/AuthController.php 

```php
<?php

namespace FintelAuth\UserManagementAuth\Controllers;

use FintelAuth\UserManagementAuth\UserManagementAuthClient;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class AuthController
{
    private $authClient;
    private $clientId;
    private $clientSecret;
    private $redirectUri;

    public function __construct(
        UserManagementAuthClient $authClient,
        string $clientId,
        string $clientSecret,
        string $redirectUri
    ) {
        $this->authClient = $authClient;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $redirectUri;
    }

    public function redirectToAuth()
    {
        $url = $this->authClient->getAuthorizationUrl(
            $this->clientId,
            $this->redirectUri
        );
        return new RedirectResponse($url);
    }

    public function handleAuthCallback(Request $request)
    {
        $code = $request->get('code');

        try {
            $token = $this->authClient->getAccessToken(
                $this->clientId,
                $this->clientSecret,
                $this->redirectUri,
                $code
            );

            if (!$token) {
                return new RedirectResponse('/', 302, ['X-Error' => 'Unable to obtain access token']);
            }

            // In a real application, you'd store the token securely here

            return new RedirectResponse('/', 302, ['X-Success' => 'Successfully authenticated']);
        } catch (\Exception $e) {
            return new RedirectResponse('/', 302, ['X-Error' => 'An error occurred during authentication']);
        }
    }
}

`````

File :src/UserManagementAuth/UserManagementAuthClient.php 


```php

<?php

namespace FintelAuth\UserManagementAuth;

use Illuminate\Support\Facades\Http;

class UserManagementAuthClient
{
    private $baseUrl;

    public function __construct($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    public function validateToken($token)
    {
        $response = Http::withToken($token)->get("{$this->baseUrl}/api/validate-token");
        return $response->successful() ? $response->json() : null;
    }

    public function getAuthorizationUrl($clientId, $redirectUri, $scope = '')
    {
        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => $scope,
        ]);

        return "{$this->baseUrl}/oauth/authorize?{$query}";
    }

    public function getAccessToken($clientId, $clientSecret, $redirectUri, $code)
    {
        $response = Http::post("{$this->baseUrl}/oauth/token", [
            'grant_type' => 'authorization_code',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
            'code' => $code,
        ]);

        return $response->successful() ? $response->json() : null;
    }
}
```

File:src/UserManagementAuth/UserManagementAuthServiceProvider.php

```php

<?php

namespace FintelAuth\UserManagementAuth;

use Illuminate\Support\ServiceProvider;

class UserManagementAuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/usermanagement-auth.php' => config_path('usermanagement-auth.php'),
        ], 'config');

        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/usermanagement-auth.php', 'usermanagement-auth'
        );

        $this->app->singleton(UserManagementAuthClient::class, function ($app) {
            return new UserManagementAuthClient(config('usermanagement-auth.base_url'));
        });
    }
}
```

routes 
File :src/routes.php

```php

<?php
use Illuminate\Support\Facades\Route;
use FintelAuth\UserManagementAuth\Controllers\AuthController;

Route::get('/login', [AuthController::class, 'redirectToAuth'])->name('login');
Route::get('/callback', [AuthController::class, 'handleAuthCallback'])->name('auth.callback');
```


File :tests

```php
<?php

namespace FintelAuth\UserManagementAuth\Tests\Unit\Controllers;

use FintelAuth\UserManagementAuth\Controllers\AuthController;
use FintelAuth\UserManagementAuth\UserManagementAuthClient;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class AuthControllerTest extends TestCase
{
    protected $authClientMock;
    protected $authController;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var UserManagementAuthClient&MockInterface */
        $this->authClientMock = Mockery::mock(UserManagementAuthClient::class);
        $this->authController = new AuthController(
            $this->authClientMock,
            'test_client_id',
            'test_client_secret',
            'http://test.com/callback'
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testRedirectToAuthRedirectsToCorrectURL()
    {
        $expectedUrl = 'http://auth.example.com/oauth/authorize';

        $this->authClientMock->shouldReceive('getAuthorizationUrl')
            ->with('test_client_id', 'http://test.com/callback')
            ->once()
            ->andReturn($expectedUrl);

        $response = $this->authController->redirectToAuth();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals($expectedUrl, $response->getTargetUrl());
    }

    public function testHandleAuthCallbackSucceedsWithValidCode()
    {
        $mockRequest = Mockery::mock(Request::class);
        $mockRequest->shouldReceive('get')->with('code')->andReturn('test_code');

        $mockToken = ['access_token' => 'test_access_token'];

        $this->authClientMock->shouldReceive('getAccessToken')
            ->with('test_client_id', 'test_client_secret', 'http://test.com/callback', 'test_code')
            ->once()
            ->andReturn($mockToken);

        $response = $this->authController->handleAuthCallback($mockRequest);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/', $response->getTargetUrl());
        $this->assertEquals('Successfully authenticated', $response->headers->get('X-Success'));
    }

    public function testHandleAuthCallbackFailsWithInvalidCode()
    {
        $mockRequest = Mockery::mock(Request::class);
        $mockRequest->shouldReceive('get')->with('code')->andReturn('invalid_code');

        $this->authClientMock->shouldReceive('getAccessToken')
            ->with('test_client_id', 'test_client_secret', 'http://test.com/callback', 'invalid_code')
            ->once()
            ->andReturnNull();

        $response = $this->authController->handleAuthCallback($mockRequest);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/', $response->getTargetUrl());
        $this->assertEquals('Unable to obtain access token', $response->headers->get('X-Error'));
    }

    public function testHandleAuthCallbackHandlesExceptionsGracefully()
    {
        $mockRequest = Mockery::mock(Request::class);
        $mockRequest->shouldReceive('get')->with('code')->andReturn('test_code');

        $this->authClientMock->shouldReceive('getAccessToken')
            ->with('test_client_id', 'test_client_secret', 'http://test.com/callback', 'test_code')
            ->once()
            ->andThrow(new \Exception('API Error'));

        $response = $this->authController->handleAuthCallback($mockRequest);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/', $response->getTargetUrl());
        $this->assertEquals('An error occurred during authentication', $response->headers->get('X-Error'));
    }
}
```

File:tests/Pest.php

```php

<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

// uses(Tests\TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}
```


The microservice that we wanted to build is for company management, your target is to create microservice in Laravel v11 , to manage company, logic Complete CRUD - add company,edit company, update company,delete company 

```SQL
CREATE TABLE `companies` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`address` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`email` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`website` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`phone` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`created_at` TIMESTAMP NULL DEFAULT NULL,
	`updated_at` TIMESTAMP NULL DEFAULT NULL,
	`deleted_at` TIMESTAMP NULL DEFAULT NULL,
	`note` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`status` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`has_multiple_subscriptions` TINYINT(1) NOT NULL DEFAULT '0',
	`original_admin_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `companies_original_admin_id_foreign` (`original_admin_id`) USING BTREE,
	CONSTRAINT `companies_original_admin_id_foreign` FOREIGN KEY (`original_admin_id`) REFERENCES `users` (`id`) ON UPDATE NO ACTION ON DELETE SET NULL
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=14


CREATE TABLE `company_members` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`company_id` BIGINT(20) UNSIGNED NOT NULL,
	`user_id` BIGINT(20) UNSIGNED NOT NULL,
	`role` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`created_at` TIMESTAMP NULL DEFAULT NULL,
	`updated_at` TIMESTAMP NULL DEFAULT NULL,
	`deleted_at` TIMESTAMP NULL DEFAULT NULL,
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `company_members_company_id_foreign` (`company_id`) USING BTREE,
	INDEX `company_members_user_id_foreign` (`user_id`) USING BTREE,
	CONSTRAINT `company_members_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT `company_members_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=35


CREATE TABLE `users` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`email` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`email_verified_at` TIMESTAMP NULL DEFAULT NULL,
	`is_organization` TINYINT(1) NOT NULL DEFAULT '0',
	`is_personal` TINYINT(1) NOT NULL DEFAULT '0',
	`company_id` BIGINT(20) UNSIGNED NOT NULL,
	`password` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`remember_token` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`status` VARCHAR(255) NOT NULL DEFAULT 'active' COLLATE 'utf8mb4_unicode_ci',
	`created_at` TIMESTAMP NULL DEFAULT NULL,
	`updated_at` TIMESTAMP NULL DEFAULT NULL,
	PRIMARY KEY (`id`) USING BTREE,
	UNIQUE INDEX `users_email_unique` (`email`) USING BTREE
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=32
;

;

;

``` 

for the company management microservice, you need to create the following files: 
- config/company-management-auth.php
- src/Middleware/ValidateCompanyManagementToken.php
- src/CompanyManagement/Controllers/AuthController.php
- src/CompanyManagement/CompanyManagementClient.php
- src/CompanyManagement/CompanyManagementServiceProvider.php
- src/routes.php

- tests/Pest.php

### BASE - this is the files that we already have in the projects.

#### The API layers as follows 


#### 1. controllers

- app/Http/Controllers/CompanyController.php

```php
<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index()
    {
        return Company::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|string',
        ]);

        $company = Company::create($validated);

        return response()->json($company, 201);
    }

    public function show($id)
    {
        $company = Company::findOrFail($id);
        return response()->json($company);
    }

    public function update(Request $request, $id)
    {
        $company = Company::findOrFail($id);
        $company->update($request->all());

        return response()->json($company, 200);
    }

    public function destroy($id)
    {
        $company = Company::findOrFail($id);
        $company->delete();

        return response()->json(null, 204);
    }
}

```

-2 app/Http/Controllers/CompanyMemberController.php

```php 
<?php
namespace App\Http\Controllers;

use App\Models\CompanyMember;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyMemberController extends Controller
{
    public function index()
    {
        // Get all company members
        $members = CompanyMember::with('company', 'user')->get();
        return response()->json($members, 200);
    }

    public function store(Request $request)
    {
        // Validate input data
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'user_id' => 'required|exists:users,id',
            'role' => 'nullable|string|max:255',
        ]);

        // Create new company member
        $member = CompanyMember::create($validated);

        return response()->json($member, 201);
    }

    public function show($id)
    {
        // Find and return a specific company member
        $member = CompanyMember::with('company', 'user')->findOrFail($id);
        return response()->json($member, 200);
    }

    public function update(Request $request, $id)
    {
        // Find the company member
        $member = CompanyMember::findOrFail($id);

        // Validate input data
        $validated = $request->validate([
            'role' => 'nullable|string|max:255',
        ]);

        // Update the member's role
        $member->update($validated);

        return response()->json($member, 200);
    }

    public function destroy($id)
    {
        // Find the company member
        $member = CompanyMember::findOrFail($id);

        // Delete the member
        $member->delete();

        return response()->json(null, 204);
    }
}

```

models

- app/Models/Company.php
```php 
<?php

namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;
    use SoftDeletes;
    //
    protected $table='companies';

    protected $fillable = [
        'name', 'address', 'email', 'website', 'phone', 'note', 
        'status', 'has_multiple_subscriptions', 'original_admin_id'
    ];

    public function members()
    {
        return $this->hasMany(CompanyMember::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'original_admin_id');
    }

      // Query scopes
      public function scopeActive($query)
      {
          return $query->where('status', 'active');
      }
  
      public function scopeWithMultipleSubscriptions($query)
      {
          return $query->where('has_multiple_subscriptions', true);
      }
  
      // Validation rules (can be used in form requests)
      public static function rules()
      {
          return [
              'name' => 'required|string|max:255',
              'email' => 'required|email|unique:companies,email|max:255',
              'phone' => 'nullable|string|max:15',
              'website' => 'nullable|url|max:255',
              // Add other validation rules as necessary
          ];
      }
}

```

- app/Models/CompanyMember.php
```php 
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompanyMembers extends Model
{
    //
    use SoftDeletes;
    use HasFactory;

    protected $table = 'company_members';

    protected $fillable = ['company_id', 'user_id', 'role'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}


```


#### 2. services

#### 3. routes

```php
<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

```
we need to configure routes for the company management microservice.using API routes.

 the api routes should be in the routes/api.php file.

api.php file as following:

```php

```


