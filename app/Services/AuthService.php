<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;

class AuthService extends BaseService
{
    public function __construct(UserRepositoryInterface $userRepository)
    {
        parent::__construct($userRepository);
    }

    /**
     * @param  array{name: string, email: string, password: string}  $data
     * @return array{user: User, token: string}
     */
    public function register(array $data): array
    {
        /** @var User $user */
        $user = $this->repository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);
       $user->markEmailAsVerified();

event(new Registered($user));

// Send welcome email
try {
    \Illuminate\Support\Facades\Mail::to($user->email)
        ->send(new \App\Mail\WelcomeMail($user));
} catch (\Exception $e) {
    \Illuminate\Support\Facades\Log::error('Welcome email failed', [
        'user_id' => $user->id,
        'error'   => $e->getMessage(),
    ]);
}

        return [
            'user' => $user,
            'token' => $this->createAuthToken($user)->plainTextToken,
        ];
    }

    /**
     * @return array{user: User, token: string}
     */
    public function login(string $email, string $password, bool $rememberMe = false): array
    {
        $user = $this->repository->findByEmail($email);

        if ($user === null || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        Auth::login($user, $rememberMe);

        $expiresAt = $rememberMe ? now()->addDays(30) : null;

        return [
            'user' => $user,
            'token' => $this->createAuthToken($user, $expiresAt)->plainTextToken,
        ];
    }

    public function logout(User $user): void
    {
        /** @var PersonalAccessToken|null $token */
        $token = $user->currentAccessToken();

        $token?->delete();
    }

    public function logoutAll(User $user): void
    {
        $user->tokens()->delete();
    }

    public function sendPasswordResetLink(string $email): string
    {
        $status = Password::sendResetLink(['email' => $email]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return __($status);
    }

    /**
     * @param  array{email: string, password: string, token: string}  $data
     */
    public function resetPassword(array $data): string
    {
        $status = Password::reset(
            $data,
            function (User $user, string $password): void {
                $this->repository->updateModel($user, [
                    'password' => $password,
                ]);

                $user->tokens()->delete();

                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return __($status);
    }

    public function verifyEmail(User $user, string $hash): void
    {
        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid verification link.'],
            ]);
        }

        if ($user->hasVerifiedEmail()) {
            return;
        }

        $user->markEmailAsVerified();
    }

    public function sendVerificationEmail(User $user): void
    {
        if ($user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => ['Email address is already verified.'],
            ]);
        }

        $user->sendEmailVerificationNotification();
    }

    protected function createAuthToken(User $user, ?\DateTimeInterface $expiresAt = null): NewAccessToken
    {
        return $user->createToken('auth-token', ['*'], $expiresAt);
    }
}
