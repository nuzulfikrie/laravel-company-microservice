<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * This middleware authenticates the user by verifying the personal access token
     * with the UserManagement microservice and checks the user's status and access level.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Unauthorized: No token provided'], Response::HTTP_UNAUTHORIZED);
        }

        // Verify token with UserManagement service
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get(config('services.user_management.url') . '/api/auth/verify-token');

        if ($response->failed()) {
            return response()->json(['error' => 'Unauthorized: Invalid token'], Response::HTTP_UNAUTHORIZED);
        }

        $user = $response->json();

        if ($user['status'] !== 'active') {
            return response()->json(['error' => 'User account is not active'], Response::HTTP_FORBIDDEN);
        }

        $request->merge(['user' => $user]);

        return $next($request);
    }
} 