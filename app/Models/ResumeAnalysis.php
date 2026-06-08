<?php

namespace App\Models;

use Database\Factories\ResumeAnalysisFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResumeAnalysis extends Model
{
    /** @use HasFactory<ResumeAnalysisFactory> */
    use HasFactory;

    protected $fillable = [
        'resume_id',
        'user_id',
        'job_description_id',
        'ats_score',
        'status',
        'strengths',
        'weaknesses',
        'missing_keywords',
        'recommendations',
        'raw_ai_response',
        'analyzed_at',
    ];

    protected function casts(): array
    {
        return [
            'ats_score'          => 'integer',
            'strengths'          => 'array',
            'weaknesses'         => 'array',
            'missing_keywords'   => 'array',
            'recommendations'    => 'array',
            'analyzed_at'        => 'datetime',
        ];
    }

    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resume::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jobDescription(): BelongsTo
    {
        return $this->belongsTo(JobDescription::class);
    }
}