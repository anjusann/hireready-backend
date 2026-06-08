<?php

namespace App\Models;

use App\Enums\ResumeFileType;
use Database\Factories\ResumeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resume extends Model
{
    /** @use HasFactory<ResumeFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'file_path',
        'file_type',
        'file_size',
        'extracted_text',
        'is_primary',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'file_type' => ResumeFileType::class,
            'file_size' => 'integer',
            'is_primary' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function analyses(): HasMany
    {
        return $this->hasMany(ResumeAnalysis::class);
    }

    public function coverLetters(): HasMany
    {
        return $this->hasMany(CoverLetter::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }
}
