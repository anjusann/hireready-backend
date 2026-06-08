<?php

namespace Database\Factories;

use App\Enums\SubscriptionPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'avatar' => fake()->optional()->imageUrl(200, 200, 'people'),
            'phone' => fake()->optional()->phoneNumber(),
            'location' => fake()->optional()->city().', UAE',
            'linkedin_url' => fake()->optional()->url(),
            'subscription_plan' => fake()->randomElement(SubscriptionPlan::cases()),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function pro(): static
    {
        return $this->state(fn (array $attributes) => [
            'subscription_plan' => SubscriptionPlan::Pro,
        ]);
    }

    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'subscription_plan' => SubscriptionPlan::Free,
        ]);
    }
}
