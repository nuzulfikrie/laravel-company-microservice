<?php

namespace Tests\Unit\Middleware;

use Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Middleware\ServiceAuthMiddleware;

class ValidateCompanyManagementTokenTest extends TestCase
{
    private ServiceAuthMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new ServiceAuthMiddleware();

        config([
            'services.user_management.base_url' => 'http://user-management',
            'services.user_management.key' => 'test-key',
            'services.user_management.id' => 'test-id',
        ]);
    }

    public function test_middleware_passes_with_valid_token()
    {
        Http::fake([
            '*' => Http::response([
                'id' => 1,
                'email' => 'test@example.com',
                'roles' => ['super-admin']
            ], 200)
        ]);

        $request = Request::create('/test', 'GET');
        $request->headers->set('Authorization', 'Bearer valid-token');

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(200, $response->status());
    }

    public function test_middleware_fails_with_invalid_token()
    {
        Http::fake([
            '*' => Http::response(['error' => 'Unauthorized'], 401)
        ]);

        $request = Request::create('/test', 'GET');
        $request->headers->set('Authorization', 'Bearer invalid-token');

        $response = $this->middleware->handle($request, function () {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(401, $response->status());
    }

    public function test_middleware_fails_without_token()
    {
        $request = Request::create('/test', 'GET');

        $response = $this->middleware->handle($request, function () {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(401, $response->status());
    }
}