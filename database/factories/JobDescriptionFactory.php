<?php

namespace Database\Factories;

use App\Models\JobDescription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobDescription>
 */
class JobDescriptionFactory extends Factory
{
    protected $model = JobDescription::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->jobTitle(),
            'company' => fake()->company(),
            'location' => fake()->city().', UAE',
            'description' => fake()->paragraphs(4, true),
            'requirements' => fake()->randomElements([
                'Bachelor\'s degree in related field',
                '3+ years of experience',
                'Strong communication skills',
                'Proficiency in Microsoft Office',
                'Team leadership experience',
            ], fake()->numberBetween(3, 5)),
            'salary_range' => fake()->optional()->numerify('AED #,### - #,###'),
            'job_url' => fake()->optional()->url(),
        ];
    }
}
