<?php

namespace App\Models;

use App\Enums\CoverLetterTone;
use Database\Factories\CoverLetterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoverLetter extends Model
{
    /** @use HasFactory<CoverLetterFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'resume_id',
        'job_description_id',
        'content',
        'tone',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tone' => CoverLetterTone::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resume::class);
    }

    public function jobDescription(): BelongsTo
    {
        return $this->belongsTo(JobDescription::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }
}
