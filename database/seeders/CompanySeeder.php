<?php

namespace Database\Seeders;

use App\Enums\CompanyMemberEnum;
use App\Enums\UserEnum;
use App\Models\Company;
use App\Models\CompanyMember;
use App\Models\User;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        // Create companies with members
        Company::factory(10)->create()->each(function ($company) {
            $this->createCompanyMembers($company);
        });
    }

    private function createCompanyMembers(Company $company): void
    {
        // Get or create users
        $users = User::factory(5)->create();

        // Assign first user as admin
        $firstUser = $users->first();
        if ($firstUser) {
            CompanyMember::factory()->create([
                'company_id' => $company->id,
                'user_id' => $firstUser->id,
                'role' => CompanyMemberEnum::getAdminRole(),
            ]);

            // Update the company's original admin ID
            $company->update(['original_admin_id' => $firstUser->id]);
        }

        // Assign other users as members
        $this->assignMembersToCompany($company, $users);
    }

    private function assignMembersToCompany(Company $company, $users): void
    {
        // Get eligible users (not Super Admin and not already members)
        $eligibleUsers = $users->filter(function ($user) use ($company) {
            return ! $user->hasRole('Super Admin') &&
                ! CompanyMember::where('company_id', $company->id)
                    ->where('user_id', $user->id)
                    ->exists();
        });

        // Create company members
        $eligibleUsers->take(3)->each(function ($user) use ($company) {
            CompanyMember::factory()->create([
                'company_id' => $company->id,
                'user_id' => $user->id,
                'role' => CompanyMemberEnum::getMemberRole(),
            ]);
        });
    }
}
