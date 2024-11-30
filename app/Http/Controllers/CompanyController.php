<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\CreateCompanyRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Enums\CompanyEnum;
use Exception;

/**
 * Class CompanyController
 *
 * Handles CRUD operations for Company entities.
 *
 * @package App\Http\Controllers
 */
class CompanyController extends Controller
{

    private function getUserData(Request $request): ?array
    {
        return $request->get('user_data');
    }

    private function hasPermission($userData, string $permission): bool
    {
        if (!$userData) {
            return false;
        }

        if (is_array($userData)) {
            return in_array($permission, $userData['permissions'] ?? []);
        }

        // Get user_data from request if we got User model
        $requestUserData = request()->get('user_data');
        return $requestUserData && is_array($requestUserData) &&
            in_array($permission, $requestUserData['permissions'] ?? []);
    }


    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $userData = $this->getUserData($request);
            if (!$userData) {
                Log::error('No authenticated user found in request');
                return response()->json(['error' => 'Unauthorized access'], 401);
            }

            if (!$this->hasPermission($userData, 'view company')) {
                Log::warning('Unauthorized company view attempt', [
                    'user_id' => $userData['id'],
                    'roles' => $userData['roles']
                ]);
                return response()->json(['error' => 'This action is unauthorized.'], 403);
            }

            $companies = Company::all();
            return response()->json([
                'data' => $companies,
                'message' => 'Companies fetched successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch companies',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created company in storage.
     *
     * @param  \App\Http\Requests\CreateCompanyRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreateCompanyRequest $request): JsonResponse
    {
        try {
            $userData = $this->getUserData($request);

            if (!$userData) {
                Log::error('No authenticated user found in request');
                return response()->json(['error' => 'Unauthorized access'], 401);
            }

            if (!$this->hasPermission($userData, 'create company')) {
                Log::warning('Unauthorized company creation attempt', [
                    'user_id' => $userData['id'],
                    'roles' => $userData['roles']
                ]);
                return response()->json(['error' => 'This action is unauthorized.'], 403);
            }

            $company = Company::create($request->validated());

            return response()->json([
                'data' => $company,
                'message' => 'Company created successfully'
            ], 201);
        } catch (Exception $e) {
            Log::error('Company creation failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to create company',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Display the specified company.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        try {
            $userData = $this->getUserData($request);
            if (!$userData) {
                Log::error('No authenticated user found in request');
                return response()->json(['error' => 'Unauthorized access'], 401);
            }

            if (!$this->hasPermission($userData, 'view company')) {
                Log::warning('Unauthorized company view attempt', [
                    'user_id' => $userData['id'],
                    'roles' => $userData['roles']
                ]);
                return response()->json(['error' => 'This action is unauthorized.'], 403);
            }

            $company = Company::findOrFail($id);

            if ($company->status === CompanyEnum::getInactive()) {
                return response()->json([
                    'error' => 'Company is inactive'
                ], 403);
            }

            return response()->json([
                'data' => $company,
                'message' => 'Company fetched successfully'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Company not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch company',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified company in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $userData = $this->getUserData($request);
            if (!$userData) {
                Log::error('No authenticated user found in request');
                return response()->json(['error' => 'Unauthorized access'], 401);
            }

            if (!$this->hasPermission($userData, 'update company')) {
                Log::warning('Unauthorized company update attempt', [
                    'user_id' => $userData['id'],
                    'roles' => $userData['roles']
                ]);
                return response()->json(['error' => 'This action is unauthorized.'], 403);
            }

            // Check if company exists
            $company = Company::findOrFail($id);

            // Validate the incoming request
            $validated = $request->validate(Company::rules());

            // Update the company
            $updated = Company::updateCompany($id, $validated);
            if (!$updated) {
                throw new \Exception('Failed to update company');
            }

            // Fetch and return updated company
            $company = Company::findOrFail($id);
            return response()->json(
                [
                    'data' => $company,
                    'message' => 'Company updated successfully'
                ],
                200
            );
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Company not found'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update company',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified company from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        try {
            $userData = $this->getUserData($request);
            if (!$userData) {
                Log::error('No authenticated user found in request');
                return response()->json(['error' => 'Unauthorized access'], 401);
            }

            if (!$this->hasPermission($userData, 'delete company')) {
                Log::warning('Unauthorized company deletion attempt', [
                    'user_id' => $userData['id'],
                    'roles' => $userData['roles']
                ]);
                return response()->json(['error' => 'This action is unauthorized.'], 403);
            }

            // Check if company exists
            $company = Company::findOrFail($id);

            // Delete the company
            Company::deleteCompany($id);
            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Company not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete company',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
