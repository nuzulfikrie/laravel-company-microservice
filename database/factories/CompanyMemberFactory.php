<?php

namespace Database\Factories;

use App\Models\CompanyMember;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyMemberFactory extends Factory
{
    protected $model = CompanyMember::class;

    public function definition()
    {
        return [
            'email' => $this->faker->unique()->safeEmail,
            'role' => 'member',
            'company_id' => $this->faker->randomElement(Company::pluck('id')),
            'user_id' => $this->faker->randomElement(User::where('role', '!=', 'Super Admin')->pluck('id')),
        ];
    }
}
