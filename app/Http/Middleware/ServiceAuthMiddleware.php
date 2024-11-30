<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class ServiceAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('Authorization');

        if (! $token) {
            return response()->json(['error' => 'Unauthorized: No token provided'], 401);
        }

        $expectedToken = Config::get('services.user_management.key');

        if ($token !== $expectedToken) {
            return response()->json(['error' => 'Unauthorized: Invalid token'], 401);
        }

        return $next($request);
    }
} 