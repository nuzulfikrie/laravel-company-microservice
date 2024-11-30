<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;
use Exception;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class VerifyTokenMiddleware
{
    private const TOKEN_CACHE_DURATION = 300;
    private const REQUIRED_USER_FIELDS = ['id', 'roles', 'permissions', 'status'];

    public function handle(Request $request, Closure $next)
    {
        try {
            $token = $this->extractToken($request);

            if (empty($token)) {
                return $this->unauthorized('No token provided');
            }

            $userData = $this->getUserData($token);
            if (!$userData) {
                return $this->unauthorized('Invalid token');
            }

            // Check if user is active
            if (!$this->isUserActive($userData)) {
                return response()->json([
                    'error' => 'User account is not active'
                ], Response::HTTP_FORBIDDEN);
            }

            // Create or update user and assign roles
            $user = $this->syncUser($userData);

            // Add user data to request
            $request->merge(['user_data' => $userData]);

            return $next($request);
        } catch (Exception $e) {
            Log::error('Token verification failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->unauthorized('Token verification failed');
        }
    }

    private function getUserData(string $token): ?array
    {
        $cacheKey = 'token_verification_' . md5($token);

        return Cache::remember($cacheKey, self::TOKEN_CACHE_DURATION, function () use ($token) {
            return $this->verifyTokenWithService($token);
        });
    }

    private function extractToken(Request $request): ?string
    {
        $header = $request->header('Authorization');

        if (empty($header)) {
            return null;
        }

        if (!preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
            Log::warning('Invalid token format', ['header' => $header]);
            return null;
        }

        return $matches[1];
    }

    private function verifyTokenWithService(string $token): ?array
    {
        $baseUrl = Config::get('services.user_management.base_url');
        $timeout = Config::get('services.user_management.timeout', 5);

        try {
            $response = Http::timeout($timeout)
                ->withHeaders([
                    'Authorization' => "Bearer {$token}",
                    'X-Service-Key' => Config::get('services.user_management.key'),
                    'X-Service-ID' => Config::get('services.user_management.id'),
                ])
                ->get("{$baseUrl}/api/auth/verify-token");

            Log::debug('Token verification response', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            if (!$response->successful()) {
                return null;
            }

            $userData = $response->json();

            // Transform access_level to roles if needed
            if (isset($userData['access_level']) && !isset($userData['roles'])) {
                $userData['roles'] = [$userData['access_level']];
            }

            // Set default permissions based on role if not provided
            if (!isset($userData['permissions'])) {
                $userData['permissions'] = $this->getDefaultPermissions($userData['roles'] ?? []);
            }

            // Ensure all required fields are present
            if (!$this->validateUserData($userData)) {
                Log::warning('Invalid user data structure', ['userData' => $userData]);
                return null;
            }

            return $userData;
        } catch (Exception $e) {
            Log::error('Token verification request failed', [
                'error' => $e->getMessage(),
                'url' => $baseUrl
            ]);
            throw $e;
        }
    }

    private function validateUserData(array $userData): bool
    {
        foreach (self::REQUIRED_USER_FIELDS as $field) {
            if (!isset($userData[$field])) {
                Log::warning('Missing required user data field', [
                    'field' => $field,
                    'userData' => $userData
                ]);
                return false;
            }
        }

        return is_array($userData['roles']) &&
            is_array($userData['permissions']);
    }

    private function isUserActive(array $userData): bool
    {
        return isset($userData['status']) && $userData['status'] === 'active';
    }

    private function syncUser(array $userData): User
    {
        $user = User::firstOrCreate(
            ['id' => $userData['id']],
            [
                'name' => $userData['name'] ?? '',
                'email' => $userData['email'] ?? '',
                'status' => $userData['status'] ?? 'active'
            ]
        );

        if (isset($userData['roles'])) {
            $user->syncRoles($userData['roles']);
        }

        return $user;
    }

    private function hasPermission(array $userData, string $permission): bool
    {
        return isset($userData['permissions']) &&
            in_array($permission, $userData['permissions']);
    }

    private function unauthorized(string $message): JsonResponse
    {
        return response()->json([
            'error' => 'Unauthorized: ' . $message
        ], Response::HTTP_UNAUTHORIZED);
    }
}
