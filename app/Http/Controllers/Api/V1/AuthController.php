<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends BaseController
{
    public function __construct(protected AuthService $authService) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return $this->successResponse([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
            'token_type' => 'Bearer',
        ], 'Registration successful', 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->validated('email'),
            $request->validated('password'),
            $request->boolean('remember_me'),
        );

        return $this->successResponse([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
            'token_type' => 'Bearer',
        ], 'Login successful');
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->successResponse(null, 'Logout successful');
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $this->authService->logoutAll($request->user());

        return $this->successResponse(null, 'Logged out from all devices');
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $message = $this->authService->sendPasswordResetLink($request->validated('email'));

        return $this->successResponse(null, $message);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $message = $this->authService->resetPassword($request->validated());

        return $this->successResponse(null, $message);
    }

    public function verifyEmail(int $id, string $hash): JsonResponse
    {
        $user = User::findOrFail($id);

        $this->authService->verifyEmail($user, $hash);

        return $this->successResponse(
            new UserResource($user->fresh()),
            'Email verified successfully',
        );
    }

    public function sendVerificationEmail(Request $request): JsonResponse
    {
        $this->authService->sendVerificationEmail($request->user());

        return $this->successResponse(null, 'Verification link sent');
    }
}
