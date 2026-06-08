<?php

namespace Tests\Unit\Services\AI;

use App\Exceptions\AiServiceException;
use App\Services\AI\GeminiService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class GeminiServiceTest extends TestCase
{
    private GeminiService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'gemini.api_key' => 'test-api-key',
            'gemini.model' => 'gemini-2.5-flash',
            'gemini.base_url' => 'https://generativelanguage.googleapis.com/v1beta',
            'gemini.timeout' => 30,
            'gemini.retry_times' => 3,
        ]);

        Log::spy();

        $this->service = new GeminiService;
    }

    public function test_analyze_resume_returns_validated_response(): void
    {
        $this->fakeGeminiResponse([
            'ats_score' => 75,
            'strengths' => ['Clear structure'],
            'weaknesses' => ['Missing metrics'],
            'missing_keywords' => ['Agile'],
            'recommendations' => ['Add quantifiable results'],
            'experience_level' => 'mid',
            'suggested_roles' => ['Project Manager'],
        ]);

        $result = $this->service->analyzeResume('Sample resume text');

        $this->assertSame(75, $result['ats_score']);
        $this->assertSame('mid', $result['experience_level']);
        $this->assertSame(['Clear structure'], $result['strengths']);

        Log::shouldHaveReceived('info')->atLeast()->once();
    }

    public function test_generate_cover_letter_returns_validated_response(): void
    {
        $this->fakeGeminiResponse([
            'cover_letter' => 'Dear Hiring Manager, ...',
            'subject_line' => 'Application for Software Engineer',
            'key_highlights' => ['5 years experience', 'UAE market knowledge'],
        ]);

        $result = $this->service->generateCoverLetter(
            'Resume text',
            'Job description text',
            'professional',
        );

        $this->assertSame('Dear Hiring Manager, ...', $result['cover_letter']);
        $this->assertSame('Application for Software Engineer', $result['subject_line']);
    }

    public function test_generate_interview_questions_returns_validated_response(): void
    {
        $this->fakeGeminiResponse([
            'questions' => [
                [
                    'question' => 'Describe a challenging project.',
                    'category' => 'behavioral',
                    'suggested_answer' => 'Use STAR method.',
                    'tips' => 'Be concise.',
                ],
                [
                    'question' => 'Explain REST APIs.',
                    'category' => 'technical',
                    'suggested_answer' => 'REST is an architectural style.',
                    'tips' => 'Mention HTTP verbs.',
                ],
            ],
        ]);

        $result = $this->service->generateInterviewQuestions('Software Engineer', 'medium', 2);

        $this->assertCount(2, $result['questions']);
        $this->assertSame('behavioral', $result['questions'][0]['category']);
    }

    public function test_match_resume_with_job_returns_validated_response(): void
    {
        $this->fakeGeminiResponse([
            'match_score' => 80,
            'matching_skills' => ['PHP', 'Laravel'],
            'missing_skills' => ['Kubernetes'],
            'recommendations' => ['Highlight cloud experience'],
            'suitability' => 'high',
        ]);

        $result = $this->service->matchResumeWithJob('Resume text', 'Job description');

        $this->assertSame(80, $result['match_score']);
        $this->assertSame('high', $result['suitability']);
    }

    public function test_throws_exception_when_api_key_missing(): void
    {
        config(['gemini.api_key' => null]);

        $this->expectException(AiServiceException::class);
        $this->expectExceptionMessage('Gemini API key is not configured');

        $this->service->analyzeResume('Resume text');
    }

    public function test_throws_exception_on_http_error(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(['error' => 'Bad Request'], 400),
        ]);

        $this->expectException(AiServiceException::class);
        $this->expectExceptionMessage('Gemini API returned an error');

        $this->service->analyzeResume('Resume text');
    }

    public function test_throws_exception_on_invalid_json_response(): void
    {
        $this->fakeGeminiRawText('not-json');

        $this->expectException(AiServiceException::class);
        $this->expectExceptionMessage('Gemini returned invalid JSON');

        $this->service->analyzeResume('Resume text');
    }

    public function test_throws_exception_on_missing_response_fields(): void
    {
        $this->fakeGeminiResponse([
            'ats_score' => 75,
            'strengths' => ['Clear structure'],
        ]);

        $this->expectException(AiServiceException::class);
        $this->expectExceptionMessage('Missing required key');

        $this->service->analyzeResume('Resume text');
    }

    public function test_request_includes_json_generation_config(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response($this->geminiApiEnvelope([
                'ats_score' => 70,
                'strengths' => ['A'],
                'weaknesses' => ['B'],
                'missing_keywords' => ['C'],
                'recommendations' => ['D'],
                'experience_level' => 'junior',
                'suggested_roles' => ['Developer'],
            ])),
        ]);

        $this->service->analyzeResume('Resume text');

        Http::assertSent(function ($request) {
            $body = $request->data();

            return $request->hasHeader('Accept', 'application/json')
                && data_get($body, 'generationConfig.responseMimeType') === 'application/json'
                && str_contains($request->url(), 'key=test-api-key')
                && str_contains($request->url(), 'models/gemini-2.5-flash:generateContent');
        });
    }

    /**
     * @param  array<string, mixed>  $json
     */
    private function fakeGeminiResponse(array $json): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response($this->geminiApiEnvelope($json)),
        ]);
    }

    private function fakeGeminiRawText(string $text): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => $text],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);
    }

    /**
     * @param  array<string, mixed>  $json
     * @return array<string, mixed>
     */
    private function geminiApiEnvelope(array $json): array
    {
        return [
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            ['text' => json_encode($json, JSON_THROW_ON_ERROR)],
                        ],
                    ],
                ],
            ],
        ];
    }
}
