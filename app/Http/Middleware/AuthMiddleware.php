<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['error' => 'Unauthorized: No token provided'], Response::HTTP_UNAUTHORIZED);
        }

        $response = Http::withToken($token)->get(config('services.user_management.base_url') . '/api/user');

        if (! $response->successful()) {
            return response()->json(['error' => 'Unauthorized: Invalid token'], Response::HTTP_UNAUTHORIZED);
        }

        $userData = $response->json();
        $request->merge(['user_data' => $userData]);

        return $next($request);
    }
} 