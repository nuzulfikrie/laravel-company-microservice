<?php

namespace Tests\Traits;

use Illuminate\Support\Facades\Http;

trait MocksUserManagementService
{
    protected function mockUserManagementService(): void
    {
        Http::fake([
            '*' => function ($request) {
                return Http::response([
                    'id' => 1,
                    'email' => 'test@example.com',
                    'roles' => ['super-admin'],
                    'status' => 'active',
                ]);
            },
        ]);
    }
}
