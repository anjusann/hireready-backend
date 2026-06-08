<?php

namespace App\Models;

use App\Enums\SubscriptionPlan;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'phone',
        'location',
        'linkedin_url',
        'subscription_plan',
        'email_verified_at',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'subscription_plan' => SubscriptionPlan::class,
        ];
    }

    public function resumes(): HasMany
    {
        return $this->hasMany(Resume::class);
    }

    public function resumeAnalyses(): HasMany
    {
        return $this->hasMany(ResumeAnalysis::class);
    }

    public function jobDescriptions(): HasMany
    {
        return $this->hasMany(JobDescription::class);
    }

    public function coverLetters(): HasMany
    {
        return $this->hasMany(CoverLetter::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function interviewPreparations(): HasMany
    {
        return $this->hasMany(InterviewPreparation::class);
    }
}
