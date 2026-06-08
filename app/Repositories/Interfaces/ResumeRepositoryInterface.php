<?php

namespace App\Repositories\Interfaces;

use App\Models\Resume;
use Illuminate\Database\Eloquent\Collection;

interface ResumeRepositoryInterface extends BaseRepositoryInterface
{
    public function getAllForUser(int $userId): Collection;

    public function findForUser(int $id, int $userId): ?Resume;

    public function findForUserOrFail(int $id, int $userId): Resume;

    public function countForUser(int $userId): int;

    public function updateModel(Resume $resume, array $data): Resume;

    public function clearPrimaryForUser(int $userId): void;
}
