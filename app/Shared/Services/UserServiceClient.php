<?php

namespace Shared\Services;

use Illuminate\Support\Facades\Http;

class UserServiceClient
{
    protected string $baseUrl;
    protected string $serviceKey;
    protected string $serviceId;

    public function __construct()
    {
        $this->baseUrl = config('services.user_management.base_url');
        $this->serviceKey = config('services.user_management.key');
        $this->serviceId = config('services.user_management.id');
    }

    /**
     * Validate user token
     */
    public function validateToken(string $token): array
    {
        $response = Http::withHeaders([
            'X-Service-Key' => $this->serviceKey,
            'X-Service-ID' => $this->serviceId,
            'Authorization' => "Bearer {$token}"
        ])->get("{$this->baseUrl}/api/validate-token");

        return $response->json();
    }

    /**
     * Get user details
     */
    public function getUser(string $token): ?array
    {
        $response = Http::withHeaders([
            'X-Service-Key' => $this->serviceKey,
            'X-Service-ID' => $this->serviceId,
            'Authorization' => "Bearer {$token}"
        ])->get("{$this->baseUrl}/api/user");

        return $response->successful() ? $response->json() : null;
    }

    /**
     * Check user permissions
     */
    public function checkPermission(string $token, string $permission): bool
    {
        $response = Http::withHeaders([
            'X-Service-Key' => $this->serviceKey,
            'X-Service-ID' => $this->serviceId,
            'Authorization' => "Bearer {$token}"
        ])->post("{$this->baseUrl}/api/check-permission", [
            'permission' => $permission
        ]);

        return $response->successful() && $response->json('has_permission', false);
    }

    public function userIsAdmin(string $token): bool
    {
        $response = Http::withHeaders([
            'X-Service-Key' => $this->serviceKey,
            'X-Service-ID' => $this->serviceId,
            'Authorization' => "Bearer {$token}"
        ])->get("{$this->baseUrl}/api/user");


        $adminRole = ['Super Admin', 'Admin', 'Moderator'];

        return $response->successful() && in_array($response->json('role'), $adminRole);
    }
}
