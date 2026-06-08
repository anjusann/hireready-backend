<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Database\Factories\ApplicationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Application extends Model
{
    /** @use HasFactory<ApplicationFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'job_description_id',
        'resume_id',
        'cover_letter_id',
        'company_name',
        'job_title',
        'status',
        'applied_date',
        'notes',
        'follow_up_date',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'applied_date' => 'date',
            'follow_up_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jobDescription(): BelongsTo
    {
        return $this->belongsTo(JobDescription::class);
    }

    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resume::class);
    }

    public function coverLetter(): BelongsTo
    {
        return $this->belongsTo(CoverLetter::class);
    }

    public function interviewPreparations(): HasMany
    {
        return $this->hasMany(InterviewPreparation::class);
    }
}
