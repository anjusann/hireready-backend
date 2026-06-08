<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Requests\Auth\UploadAvatarRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends BaseController
{
    public function __construct(protected UserService $userService) {}

    public function show(Request $request): JsonResponse
    {
        $user = $this->userService->getProfile($request->user());

        return $this->successResponse(
            new UserResource($user),
            'Profile retrieved successfully',
        );
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->userService->updateProfile(
            $request->user(),
            $request->validated(),
        );

        return $this->successResponse(
            new UserResource($user),
            'Profile updated successfully',
        );
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $this->userService->updatePassword(
            $request->user(),
            $request->validated('current_password'),
            $request->validated('password'),
        );

        return $this->successResponse(null, 'Password updated successfully');
    }

    public function uploadAvatar(UploadAvatarRequest $request): JsonResponse
    {
        $user = $this->userService->uploadAvatar(
            $request->user(),
            $request->file('avatar'),
        );

        return $this->successResponse(
            new UserResource($user),
            'Avatar uploaded successfully',
        );
    }
}
