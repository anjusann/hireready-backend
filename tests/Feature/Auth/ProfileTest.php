<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private function verifiedUserWithToken(): array
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        return [$user, $token];
    }

    public function test_authenticated_user_can_get_profile(): void
    {
        [$user, $token] = $this->verifiedUserWithToken();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/profile');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonMissingPath('data.password');
    }

    public function test_unauthenticated_user_cannot_get_profile(): void
    {
        $response = $this->getJson('/api/v1/profile');

        $response->assertUnauthorized();
    }

    public function test_unverified_user_cannot_get_profile(): void
    {
        $user = User::factory()->unverified()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/profile');

        $response->assertForbidden();
    }

    public function test_authenticated_user_can_update_profile(): void
    {
        [$user, $token] = $this->verifiedUserWithToken();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/v1/profile', [
                'name' => 'Updated Name',
                'phone' => '+971501234567',
                'location' => 'Dubai, UAE',
                'linkedin_url' => 'https://linkedin.com/in/johndoe',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.phone', '+971501234567')
            ->assertJsonPath('data.location', 'Dubai, UAE')
            ->assertJsonPath('data.linkedin_url', 'https://linkedin.com/in/johndoe');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_authenticated_user_can_update_password(): void
    {
        [$user, $token] = $this->verifiedUserWithToken();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/v1/profile/password', [
                'current_password' => 'password',
                'password' => 'NewPassword123!',
                'password_confirmation' => 'NewPassword123!',
            ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Password updated successfully');

        $this->assertTrue(Hash::check('NewPassword123!', $user->fresh()->password));
    }

    public function test_update_password_fails_with_wrong_current_password(): void
    {
        [, $token] = $this->verifiedUserWithToken();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/v1/profile/password', [
                'current_password' => 'wrong-password',
                'password' => 'NewPassword123!',
                'password_confirmation' => 'NewPassword123!',
            ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    public function test_authenticated_user_can_upload_avatar(): void
    {
        Storage::fake('public');

        [$user, $token] = $this->verifiedUserWithToken();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/profile/avatar', [
                'avatar' => UploadedFile::fake()->create('avatar.jpg', 1024, 'image/jpeg'),
            ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Avatar uploaded successfully')
            ->assertJsonStructure(['data' => ['avatar']]);

        $this->assertNotNull($user->fresh()->avatar);
        Storage::disk('public')->assertExists($user->fresh()->avatar);
    }

    public function test_avatar_upload_rejects_files_over_2mb(): void
    {
        Storage::fake('public');

        [, $token] = $this->verifiedUserWithToken();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/profile/avatar', [
                'avatar' => UploadedFile::fake()->create('avatar.jpg', 3000, 'image/jpeg'),
            ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false);
    }
}
