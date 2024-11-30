<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\ServiceAuthMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class ValidateCompanyManagementTokenTest extends TestCase
{
    private ServiceAuthMiddleware $middleware;
    private string $validToken = 'test-token';

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new ServiceAuthMiddleware();

        // Configure the expected token
        Config::set('services.user_management.key', $this->validToken);
    }

    public function test_middleware_passes_with_valid_token()
    {
        $request = Request::create('/test', 'GET');
        $request->headers->set('Authorization', $this->validToken);

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true], 200);
        });

        $this->assertEquals(200, $response->status());
        $this->assertEquals(['success' => true], $response->getData(true));
    }

    public function test_middleware_fails_with_invalid_token()
    {
        $request = Request::create('/test', 'GET');
        $request->headers->set('Authorization', 'invalid-token');

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(401, $response->status());
        $this->assertEquals(['error' => 'Unauthorized: Invalid token'], $response->getData(true));
    }

    public function test_middleware_fails_with_no_token()
    {
        $request = Request::create('/test', 'GET');

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(401, $response->status());
        $this->assertEquals(['error' => 'Unauthorized: No token provided'], $response->getData(true));
    }
}