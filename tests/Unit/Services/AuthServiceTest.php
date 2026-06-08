<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\AuthService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private MockInterface&UserRepositoryInterface $userRepository;

    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->authService = new AuthService($this->userRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_register_creates_user_and_returns_token(): void
    {
        Event::fake([Registered::class]);

        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $this->userRepository
            ->shouldReceive('create')
            ->once()
            ->with([
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'password123',
            ])
            ->andReturn($user);

        $result = $this->authService->register([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $this->assertSame($user, $result['user']);
        $this->assertNotEmpty($result['token']);
        Event::assertDispatched(Registered::class);
    }

    public function test_login_returns_user_and_token_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $this->userRepository
            ->shouldReceive('findByEmail')
            ->once()
            ->with('john@example.com')
            ->andReturn($user);

        $result = $this->authService->login('john@example.com', 'password123');

        $this->assertSame($user->id, $result['user']->id);
        $this->assertNotEmpty($result['token']);
    }

    public function test_login_throws_validation_exception_with_invalid_credentials(): void
    {
        $this->userRepository
            ->shouldReceive('findByEmail')
            ->once()
            ->with('john@example.com')
            ->andReturn(null);

        $this->expectException(ValidationException::class);

        $this->authService->login('john@example.com', 'wrong-password');
    }

    public function test_login_throws_validation_exception_with_wrong_password(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $this->userRepository
            ->shouldReceive('findByEmail')
            ->once()
            ->with('john@example.com')
            ->andReturn($user);

        $this->expectException(ValidationException::class);

        $this->authService->login('john@example.com', 'wrong-password');
    }

    public function test_logout_deletes_current_access_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token');
        $user->withAccessToken($token->accessToken);

        $this->authService->logout($user);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);
    }

    public function test_logout_all_deletes_all_tokens(): void
    {
        $user = User::factory()->create();
        $user->createToken('device-1');
        $user->createToken('device-2');

        $this->authService->logoutAll($user);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_verify_email_marks_user_as_verified(): void
    {
        $user = User::factory()->unverified()->create();

        $this->authService->verifyEmail($user, sha1($user->getEmailForVerification()));

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_verify_email_throws_for_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();

        $this->expectException(ValidationException::class);

        $this->authService->verifyEmail($user, 'invalid-hash');
    }

    public function test_send_verification_email_throws_when_already_verified(): void
    {
        $user = User::factory()->create();

        $this->expectException(ValidationException::class);

        $this->authService->sendVerificationEmail($user);
    }
}
