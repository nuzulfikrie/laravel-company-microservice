<?php

namespace Tests\Traits;

use Illuminate\Support\Facades\Http;

trait MocksUserManagementService
{
    protected function mockSuccessfulAuthorization(array $userData = null)
    {
        if ($userData === null) {
            $userData = [
                'id' => 1,
                'email' => 'test@example.com',
                'roles' => ['super-admin'],
                'status' => 'active'
            ];
        }

        Http::fake([
            '*' => Http::response($userData, 200),
        ]);

        return $userData;
    }

    protected function mockFailedAuthorization()
    {
        Http::fake([
            '*' => Http::response(['error' => 'Unauthorized'], 401),
        ]);
    }
}
