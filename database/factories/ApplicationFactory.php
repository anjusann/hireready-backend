<?php

namespace Database\Factories;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\CoverLetter;
use App\Models\JobDescription;
use App\Models\Resume;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Application>
 */
class ApplicationFactory extends Factory
{
    protected $model = Application::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::factory();

        return [
            'user_id' => $user,
            'job_description_id' => fake()->optional(0.7)->passthrough(
                JobDescription::factory()->for($user)
            ),
            'resume_id' => fake()->optional(0.8)->passthrough(
                Resume::factory()->for($user)
            ),
            'cover_letter_id' => fake()->optional(0.6)->passthrough(
                CoverLetter::factory()->for($user)
            ),
            'company_name' => fake()->company(),
            'job_title' => fake()->jobTitle(),
            'status' => fake()->randomElement(ApplicationStatus::cases()),
            'applied_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'notes' => fake()->optional()->sentence(),
            'follow_up_date' => fake()->optional(0.4)->dateTimeBetween('now', '+1 month'),
        ];
    }

    public function applied(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ApplicationStatus::Applied,
        ]);
    }

    public function interviewScheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ApplicationStatus::InterviewScheduled,
        ]);
    }
}
