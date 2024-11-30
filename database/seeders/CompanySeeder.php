<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\CompanyMember;
use App\Models\User;
use App\Enums\CompanyMemberEnum;
use App\Enums\UserEnum;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run()
    {
        // Get all existing users or create some if none exist
        $users = User::all();
        if ($users->isEmpty()) {
            $users = User::factory()->count(50)->create();
            foreach ($users as $user) {
                $user->role = UserEnum::getUser();
                $user->save();

                //assign role to user
                $user->assignRole(UserEnum::getUser());
            }
        }

        // Create test companies
        Company::factory()
            ->count(50)
            ->create()
            ->each(function ($company) use ($users) {
                // Create at least one admin if no members exist
                if (CompanyMember::where('company_id', $company->id)->count() === 0) {
                    //user random from users where has role not 'Super Admin'
                    $firstUser = User::whereDoesntHave('roles', function ($query) {
                        $query->where('name', 'Super Admin');
                    })->inRandomOrder()->first();

                    CompanyMember::factory()->create([
                        'company_id' => $company->id,
                        'user_id' => $firstUser->id,
                        'role' => CompanyMemberEnum::getAdminRole()
                    ]);

                    // Update the company's original admin ID
                    $company->update(['original_admin_id' => $firstUser->id]);
                }
            });

        // After the initial company creation, add random members
        $this->createCompanyMembersAfterCreation();
    }

    /**
     * Add members to companies, ensuring 3 to 5 members per company.
     */
    private function createCompanyMembersAfterCreation()
    {
        $companies = Company::all();
        $users = User::all();

        foreach ($companies as $company) {
            // Ensure the company has at least 3 and at most 5 members
            $currentMembers = CompanyMember::where('company_id', $company->id)->count();
            $neededMembers = max(0, rand(3, 5) - $currentMembers);

            // Get users who are not Super Admin and not already members
            $eligibleUsers = $users->filter(function ($user) use ($company) {
                return !$user->hasRole('Super Admin') &&
                    !CompanyMember::where('company_id', $company->id)
                        ->where('user_id', $user->id)
                        ->exists();
            });

            // Randomly assign members from eligible users
            $eligibleUsers->random(min($eligibleUsers->count(), $neededMembers))
                ->each(function ($user) use ($company) {
                    CompanyMember::factory()->create([
                        'company_id' => $company->id,
                        'user_id' => $user->id,
                        'role' => CompanyMemberEnum::getMemberRole()
                    ]);
                });
        }
    }
}
