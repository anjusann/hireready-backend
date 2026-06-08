<?php

namespace Database\Factories;

use App\Enums\CoverLetterTone;
use App\Models\CoverLetter;
use App\Models\JobDescription;
use App\Models\Resume;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CoverLetter>
 */
class CoverLetterFactory extends Factory
{
    protected $model = CoverLetter::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::factory();

        return [
            'user_id' => $user,
            'resume_id' => Resume::factory()->for($user),
            'job_description_id' => fake()->optional(0.7)->passthrough(
                JobDescription::factory()->for($user)
            ),
            'content' => fake()->paragraphs(5, true),
            'tone' => fake()->randomElement(CoverLetterTone::cases()),
        ];
    }
}
