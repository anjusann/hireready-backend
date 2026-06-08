<?php

namespace Database\Factories;

use App\Enums\InterviewDifficultyLevel;
use App\Models\Application;
use App\Models\InterviewPreparation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InterviewPreparation>
 */
class InterviewPreparationFactory extends Factory
{
    protected $model = InterviewPreparation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::factory();

        $questions = fake()->randomElements([
            'Tell me about yourself.',
            'Why do you want to work here?',
            'Describe a challenging project you handled.',
            'What are your salary expectations?',
            'Where do you see yourself in five years?',
        ], fake()->numberBetween(3, 5));

        return [
            'user_id' => $user,
            'application_id' => fake()->optional(0.7)->passthrough(
                Application::factory()->for($user)
            ),
            'job_role' => fake()->jobTitle(),
            'questions' => $questions,
            'answers' => array_map(fn (string $question) => fake()->paragraph(), $questions),
            'difficulty_level' => fake()->randomElement(InterviewDifficultyLevel::cases()),
        ];
    }
}
