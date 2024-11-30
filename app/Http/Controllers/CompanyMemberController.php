<?php

namespace App\Http\Controllers;

use App\Models\CompanyMember;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Enums\CompanyMemberEnum;

class CompanyMemberController extends Controller
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
     * Display a listing of all company members.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $userData = $this->getUserData($request);

            if (!$this->hasPermission($userData, 'view companygroup')) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Retrieve all company members
            $members = CompanyMember::all();
            return response()->json($members, 200);
        } catch (\Exception $e) {
            // Handle any exceptions that occur during retrieval
            Log::error('Error fetching company members: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve company members'], 500);
        }
    }

    /**
     * Store a newly created company member in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $userData = $this->getUserData($request);

            if (!$this->hasPermission($userData, 'create companygroup')) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Validate the incoming request
            $validated = $request->validate(CompanyMember::rules());

            // Create a new company member
            $member = CompanyMember::create($validated);
            return response()->json($member, 201);
        } catch (\Exception $e) {
            // Handle any exceptions that occur during creation
            Log::error('Error creating company member: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create companygroup'], 500);
        }
    }

    /**
     * Display the specified company member.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request,  $id): JsonResponse
    {
        try {
            $userData = $this->getUserData($request);

            if (!$this->hasPermission($userData, 'view companygroup')) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Retrieve the company member by ID
            $member = CompanyMember::findOrFail($id);
            return response()->json($member, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle case where company member is not found
            return response()->json(['error' => 'Company member not found'], 404);
        } catch (\Exception $e) {
            // Handle any other exceptions
            Log::error('Error fetching company member: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve companygroup'], 500);
        }
    }

    /**
     * Update the specified company member in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        // Validate the incoming request
        $validated = $request->validate(CompanyMember::rules());

        try {
            $userData = $this->getUserData($request);

            if (!$this->hasPermission($userData, 'update companygroup')) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Update the company member
            $member = CompanyMember::findOrFail($id);
            $member->update($validated);
            return response()->json($member, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle case where company member is not found
            return response()->json(['error' => 'Company member not found'], 404);
        } catch (\Exception $e) {
            // Handle any exceptions that occur during update
            Log::error('Error updating company member: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update companygroup'], 500);
        }
    }

    /**
     * Remove the specified company member from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request,   $id): JsonResponse
    {
        try {
            $userData = $this->getUserData($request);

            if (!$this->hasPermission($userData, 'delete companygroup')) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Delete the company member
            $member = CompanyMember::findOrFail($id);
            $member->delete();
            return response()->json(null, 204);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle case where company member is not found
            return response()->json(['error' => 'Company member not found'], 404);
        } catch (\Exception $e) {
            // Handle any exceptions that occur during deletion
            Log::error('Error deleting company member: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete companygroup'], 500);
        }
    }
}
