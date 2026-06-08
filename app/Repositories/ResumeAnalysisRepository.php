<?php

namespace App\Repositories;

use App\Models\ResumeAnalysis;
use App\Repositories\Interfaces\ResumeAnalysisRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ResumeAnalysisRepository implements ResumeAnalysisRepositoryInterface
{
    public function create(array $data): ResumeAnalysis
    {
        return ResumeAnalysis::create($data);
    }

    public function findByUser(int $userId): Collection
    {
        return ResumeAnalysis::where('user_id', $userId)
            ->with('resume')
            ->latest()
            ->get();
    }

    public function findByIdAndUser(int $id, int $userId): ?ResumeAnalysis
    {
        return ResumeAnalysis::where('id', $id)
            ->where('user_id', $userId)
            ->with('resume')
            ->first();
    }

    public function findByResume(int $resumeId): Collection
    {
        return ResumeAnalysis::where('resume_id', $resumeId)
            ->latest()
            ->get();
    }

    public function countTodayByUser(int $userId): int
    {
        return ResumeAnalysis::where('user_id', $userId)
            ->whereDate('created_at', today())
            ->count();
    }

    public function delete(int $id): bool
    {
        return ResumeAnalysis::destroy($id) > 0;
    }
}