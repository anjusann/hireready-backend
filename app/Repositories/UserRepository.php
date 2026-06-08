<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    protected function modelClass(): string
    {
        return User::class;
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->newQuery()->where('email', $email)->first();
    }

    public function updateModel(User $user, array $data): User
    {
        $user->update($data);

        return $user->fresh();
    }
}
