<?php

namespace Tests\Feature\Resume;

use App\Models\Resume;
use App\Models\User;
use App\Services\TextExtractionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class ResumeUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    private function authHeaders(User $user): array
    {
        $token = $user->createToken('auth-token')->plainTextToken;

        return ['Authorization' => 'Bearer '.$token];
    }

    public function test_authenticated_user_can_upload_pdf_resume(): void
    {
        Storage::fake('local');

        $mock = Mockery::mock(TextExtractionService::class);
        $mock->shouldReceive('tryExtract')->once()->andReturn('CareerAI Test Resume');
        $this->app->instance(TextExtractionService::class, $mock);

        $user = User::factory()->create();
        $pdfContent = file_get_contents(base_path('tests/Fixtures/sample.pdf'));

        $response = $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/v1/resumes/upload', [
                'file' => UploadedFile::fake()->createWithContent('resume.pdf', $pdfContent),
                'title' => 'My Resume',
            ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Resume uploaded successfully')
            ->assertJsonPath('data.title', 'My Resume')
            ->assertJsonPath('data.file_type', 'pdf')
            ->assertJsonPath('data.is_primary', true)
            ->assertJsonPath('data.has_extracted_text', true);

        $this->assertDatabaseHas('resumes', [
            'user_id' => $user->id,
            'title' => 'My Resume',
            'file_type' => 'pdf',
        ]);

        $resume = Resume::first();
        Storage::disk('local')->assertExists($resume->file_path);
        $this->assertStringContainsString('resumes/'.$user->id.'/', $resume->file_path);
    }

    public function test_upload_rejects_unsupported_file_type(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        $response = $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/v1/resumes/upload', [
                'file' => UploadedFile::fake()->create('resume.txt', 100, 'text/plain'),
            ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    public function test_upload_rejects_files_over_10mb(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        $response = $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/v1/resumes/upload', [
                'file' => UploadedFile::fake()->create('resume.pdf', 11000, 'application/pdf'),
            ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    public function test_free_user_cannot_exceed_five_resumes(): void
    {
        Storage::fake('local');

        $user = User::factory()->free()->create();
        Resume::factory()->count(5)->for($user)->create();

        $response = $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/v1/resumes/upload', [
                'file' => UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf'),
            ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    public function test_pro_user_cannot_exceed_twenty_resumes(): void
    {
        Storage::fake('local');

        $user = User::factory()->pro()->create();
        Resume::factory()->count(20)->for($user)->create();

        $response = $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/v1/resumes/upload', [
                'file' => UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf'),
            ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    public function test_upload_returns_warning_when_text_extraction_fails(): void
    {
        Storage::fake('local');

        $mock = Mockery::mock(TextExtractionService::class);
        $mock->shouldReceive('tryExtract')->once()->andReturn(null);
        $this->app->instance(TextExtractionService::class, $mock);

        $user = User::factory()->create();

        $response = $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/v1/resumes/upload', [
                'file' => UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf'),
            ]);

        $response->assertCreated()
            ->assertJsonPath('meta.warning', 'Text extraction failed. Resume saved without extracted content.')
            ->assertJsonPath('data.has_extracted_text', false);

        $this->assertDatabaseHas('resumes', [
            'user_id' => $user->id,
            'extracted_text' => null,
        ]);
    }

    public function test_unauthenticated_user_cannot_upload(): void
    {
        $response = $this->postJson('/api/v1/resumes/upload', [
            'file' => UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf'),
        ]);

        $response->assertUnauthorized();
    }

    public function test_user_cannot_access_another_users_resume(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $resume = Resume::factory()->for($owner)->create();

        $response = $this->withHeaders($this->authHeaders($otherUser))
            ->getJson('/api/v1/resumes/'.$resume->id);

        $response->assertNotFound();
    }
}
