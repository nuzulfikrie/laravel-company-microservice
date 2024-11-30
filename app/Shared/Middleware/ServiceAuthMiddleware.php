<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class ServiceAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Unauthorized: No token provided'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Service-Key' => config('services.user_management.key'),
                'X-Service-ID' => config('services.user_management.id'),
            ])->get(config('services.user_management.base_url') . '/api/auth/verify-token');

            if ($response->failed()) {
                return response()->json([
                    'error' => 'Unauthorized: Invalid token',
                    'details' => $response->json()
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Add user data to request for controllers to use
            $request->merge(['user' => $response->json()]);

            return $next($request);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Service authentication failed',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
