<?php

namespace CompanyManagement\Middleware;

use Closure;
use CompanyManagement\Clients\CompanyManagementClient;

class ValidateCompanyManagementToken
{
    private $companyClient;

    public function __construct(CompanyManagementClient $companyClient)
    {
        $this->companyClient = $companyClient;
    }

    public function handle($request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = $this->companyClient->validateToken($token);

        if (!$user) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        // Add user and their company access information to the request
        $request->merge([
            'user' => $user,
            'company_access' => $this->companyClient->getUserCompanyAccess($user['id'])
        ]);

        return $next($request);
    }
}
