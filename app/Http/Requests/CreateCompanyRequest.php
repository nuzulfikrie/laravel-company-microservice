<?php

namespace App\Http\Requests;

use App\Enums\UserEnum;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CreateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        try {
            $token = $this->header('Authorization');
            if (!$token) {
                return false;
            }

            $response = Http::withHeaders([
                'Authorization' => $token,
                'X-Service-Key' => config('services.user_management.key'),
                'X-Service-ID' => config('services.user_management.id'),
            ])->get(config('services.user_management.base_url') . '/api/auth/verify-token');

            if ($response->failed()) {
                return false;
            }

            //get user data from User Table 
            $userData = User::find($response->json()['id']);
            if (empty($userData)) {
                return false;
            }

            $userRole = $userData->role;
            return in_array($userRole, [
                UserEnum::getSuperAdmin(),
                UserEnum::getAdmin(),
                UserEnum::getModerator()
            ], true);
        } catch (\Exception $e) {
            Log::error('Authorization failed in CreateCompanyRequest: ' . $e->getMessage());
            return false;
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:companies,email|max:255',
            'status' => 'required|string|in:active,inactive',
            'has_multiple_subscriptions' => 'required|boolean',
            'original_admin_id' => 'required|exists:users,id',
            'address' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'phone' => 'nullable|string|max:255',
            'note' => 'nullable|string'
        ];
    }
}
