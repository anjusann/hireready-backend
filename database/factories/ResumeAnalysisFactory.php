<?php

namespace Database\Factories;

use App\Models\Resume;
use App\Models\ResumeAnalysis;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResumeAnalysis>
 */
class ResumeAnalysisFactory extends Factory
{
    protected $model = ResumeAnalysis::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::factory();

        return [
            'resume_id' => Resume::factory()->for($user),
            'user_id' => $user,
            'ats_score' => fake()->numberBetween(0, 100),
            'strengths' => fake()->randomElements([
                'Clear work history',
                'Strong action verbs',
                'Relevant skills section',
                'Quantified achievements',
            ], fake()->numberBetween(2, 4)),
            'weaknesses' => fake()->randomElements([
                'Missing keywords',
                'Inconsistent formatting',
                'Vague job descriptions',
                'No measurable results',
            ], fake()->numberBetween(1, 3)),
            'missing_keywords' => fake()->randomElements([
                'project management',
                'stakeholder engagement',
                'agile methodology',
                'data analysis',
            ], fake()->numberBetween(1, 4)),
            'recommendations' => fake()->randomElements([
                'Add more quantifiable metrics',
                'Include industry-specific keywords',
                'Shorten summary section',
                'Use consistent bullet formatting',
            ], fake()->numberBetween(2, 4)),
            'raw_ai_response' => fake()->optional()->paragraphs(2, true),
            'analyzed_at' => now(),
        ];
    }
}
