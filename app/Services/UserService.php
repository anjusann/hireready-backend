<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class UserService extends BaseService
{
    public function __construct(UserRepositoryInterface $userRepository)
    {
        parent::__construct($userRepository);
    }

    public function getProfile(User $user): User
    {
        return $user;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateProfile(User $user, array $data): User
    {
        /** @var UserRepositoryInterface $repository */
        $repository = $this->repository;

        return $repository->updateModel($user, $data);
    }

    public function updatePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (! Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        /** @var UserRepositoryInterface $repository */
        $repository = $this->repository;

        $repository->updateModel($user, [
            'password' => $newPassword,
        ]);
    }

    public function uploadAvatar(User $user, UploadedFile $file): User
    {
        if ($user->avatar !== null) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $file->store('avatars/'.$user->id, 'public');

        /** @var UserRepositoryInterface $repository */
        $repository = $this->repository;

        return $repository->updateModel($user, [
            'avatar' => $path,
        ]);
    }
}
