<?php

namespace App\Repositories;

use App\Models\Resume;
use App\Repositories\Interfaces\ResumeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ResumeRepository extends BaseRepository implements ResumeRepositoryInterface
{
    protected function modelClass(): string
    {
        return Resume::class;
    }

    public function getAllForUser(int $userId): Collection
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->latest()
            ->get();
    }

    public function findForUser(int $id, int $userId): ?Resume
    {
        return $this->model->newQuery()
            ->whereKey($id)
            ->where('user_id', $userId)
            ->first();
    }

    public function findForUserOrFail(int $id, int $userId): Resume
    {
        return $this->model->newQuery()
            ->whereKey($id)
            ->where('user_id', $userId)
            ->firstOrFail();
    }

    public function countForUser(int $userId): int
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->count();
    }

    public function updateModel(Resume $resume, array $data): Resume
    {
        $resume->update($data);

        return $resume->fresh();
    }

    public function clearPrimaryForUser(int $userId): void
    {
        $this->model->newQuery()
            ->where('user_id', $userId)
            ->update(['is_primary' => false]);
    }
}
