<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Enums\UserEnum;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'is_personal' => false,
            'is_organization' => false,
            'company_id' => null,
            'profile_picture' => null,
            'cover_picture' => null,
            'bio' => $this->faker->text,
            'website' => $this->faker->url,
            'location' => $this->faker->city,
            'phone' => $this->faker->phoneNumber,
            'gender' => $this->faker->randomElement(['male', 'female', 'other']),
            'birthdate' => $this->faker->date(),
            'status' => UserEnum::getActive(),
            'is_banned' => false,
            'banned_until' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
