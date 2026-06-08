<?php

namespace App\Models;

use App\Enums\InterviewDifficultyLevel;
use Database\Factories\InterviewPreparationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterviewPreparation extends Model
{
    /** @use HasFactory<InterviewPreparationFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'application_id',
        'job_role',
        'questions',
        'answers',
        'difficulty_level',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'questions' => 'array',
            'answers' => 'array',
            'difficulty_level' => InterviewDifficultyLevel::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
