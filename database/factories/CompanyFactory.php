<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition()
    {

        return [
            'name' => $this->faker->company,
            'email' => $this->faker->unique()->companyEmail,
            'website' => $this->faker->url,
            'phone' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'note' => $this->faker->text,
            'status' => 'active',
            'has_multiple_subscriptions' => false,
            'original_admin_id' => null
        ];
    }
}
