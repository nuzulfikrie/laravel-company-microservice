<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ServiceAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('Authorization');

        if (!$token) {
            return response()->json(['error' => 'Unauthorized: No token provided'], 401);
        }

        $response = Http::withHeaders([
            'Authorization' => $token,
            'X-Service-Key' => config('services.user_management.key'),
            'X-Service-ID' => config('services.user_management.id'),
        ])->get(config('services.user_management.base_url') . '/api/auth/verify-token');

        if ($response->failed()) {
            return response()->json(['error' => 'Unauthorized: Invalid token'], 401);
        }

        return $next($request);
    }
} 