<?php

namespace Database\Factories;

use App\Enums\ResumeFileType;
use App\Models\Resume;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Resume>
 */
class ResumeFactory extends Factory
{
    protected $model = Resume::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fileType = fake()->randomElement(ResumeFileType::cases());

        return [
            'user_id' => User::factory(),
            'title' => fake()->jobTitle().' Resume',
            'file_path' => 'resumes/'.fake()->uuid().'.'.$fileType->value,
            'file_type' => $fileType,
            'file_size' => fake()->numberBetween(50_000, 2_000_000),
            'extracted_text' => fake()->optional()->paragraphs(3, true),
            'is_primary' => false,
        ];
    }

    public function primary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary' => true,
        ]);
    }
}
