<?php

namespace App\Repositories\Interfaces;

interface ResumeAnalysisRepositoryInterface
{
    public function create(array $data): \App\Models\ResumeAnalysis;
    public function findByUser(int $userId): \Illuminate\Database\Eloquent\Collection;
    public function findByIdAndUser(int $id, int $userId): ?\App\Models\ResumeAnalysis;
    public function findByResume(int $resumeId): \Illuminate\Database\Eloquent\Collection;
    public function countTodayByUser(int $userId): int;
    public function delete(int $id): bool;
}