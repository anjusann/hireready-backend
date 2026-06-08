<?php

namespace App\Services\AI;

use App\Exceptions\AiServiceException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    public function analyzeResume(string $resumeText): array
    {
        $prompt = <<<PROMPT
You are an expert ATS resume analyst for the UAE job market.

Analyze the following resume and respond with ONLY valid JSON (no markdown, no code fences) matching this exact structure:
{
  "ats_score": 75,
  "strengths": ["string"],
  "weaknesses": ["string"],
  "missing_keywords": ["string"],
  "recommendations": ["string"],
  "experience_level": "junior|mid|senior",
  "suggested_roles": ["string"]
}

Rules:
- ats_score must be an integer from 0 to 100
- experience_level must be exactly one of: junior, mid, senior
- All array fields must contain strings only

Resume:
{$resumeText}
PROMPT;

        return $this->validateAnalyzeResumeResponse(
            $this->generateJson('analyzeResume', $prompt),
        );
    }

    public function generateCoverLetter(string $resumeText, string $jobDescription, string $tone): array
    {
        $prompt = <<<PROMPT
You are an expert career coach writing cover letters for the UAE job market.

Write a cover letter using a {$tone} tone and respond with ONLY valid JSON (no markdown, no code fences) matching this exact structure:
{
  "cover_letter": "full text",
  "subject_line": "string",
  "key_highlights": ["string"]
}

Rules:
- cover_letter must be the complete cover letter text
- key_highlights must be an array of strings

Resume:
{$resumeText}

Job Description:
{$jobDescription}
PROMPT;

        return $this->validateCoverLetterResponse(
            $this->generateJson('generateCoverLetter', $prompt),
        );
    }

    public function generateInterviewQuestions(string $jobRole, string $difficulty, int $count): array
    {
        $prompt = <<<PROMPT
You are an expert interview coach for the UAE job market.

Generate exactly {$count} interview questions for the role below with {$difficulty} difficulty.

Respond with ONLY valid JSON (no markdown, no code fences) matching this exact structure:
{
  "questions": [
    {
      "question": "string",
      "category": "technical|behavioral|situational",
      "suggested_answer": "string",
      "tips": "string"
    }
  ]
}

Rules:
- questions array must contain exactly {$count} items
- category must be exactly one of: technical, behavioral, situational

Job Role:
{$jobRole}
PROMPT;

        return $this->validateInterviewQuestionsResponse(
            $this->generateJson('generateInterviewQuestions', $prompt),
            $count,
        );
    }

    public function matchResumeWithJob(string $resumeText, string $jobDescription): array
    {
        $prompt = <<<PROMPT
You are an expert recruiter analyzing job fit for the UAE market.

Compare the resume against the job description and respond with ONLY valid JSON (no markdown, no code fences) matching this exact structure:
{
  "match_score": 80,
  "matching_skills": ["string"],
  "missing_skills": ["string"],
  "recommendations": ["string"],
  "suitability": "high|medium|low"
}

Rules:
- match_score must be an integer from 0 to 100
- suitability must be exactly one of: high, medium, low

Resume:
{$resumeText}

Job Description:
{$jobDescription}
PROMPT;

        return $this->validateMatchResponse(
            $this->generateJson('matchResumeWithJob', $prompt),
        );
    }

    protected function generateJson(string $method, string $prompt): array
    {
        $responseBody = $this->sendRequest($method, $prompt);
        $text = $this->extractTextFromResponse($responseBody);

        Log::info('Gemini API parsed response', [
            'method' => $method,
            'parsed_text' => $text,
        ]);

        $decoded = json_decode($text, true);

        if (! is_array($decoded)) {
            throw AiServiceException::invalidResponse('Gemini returned invalid JSON.');
        }

        return $decoded;
    }

    protected function sendRequest(string $method, string $prompt): array
    {
        $apiKey = config('gemini.api_key');

        if (empty($apiKey)) {
            throw AiServiceException::requestFailed('Gemini API key is not configured.');
        }

        $model = config('gemini.model');
        $url = sprintf(
            '%s/models/%s:generateContent',
            rtrim(config('gemini.base_url'), '/'),
            $model,
        );

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
            ],
        ];

        Log::info('Gemini API request', [
            'method' => $method,
            'model' => $model,
            'url' => $url,
            'payload' => $payload,
        ]);

        try {
            $response = Http::timeout(config('gemini.timeout'))
                ->withQueryParameters(['key' => $apiKey])
                ->retry(
                    config('gemini.retry_times'),
                    function (int $attempt): int {
                        return (int) (1000 * (2 ** ($attempt - 1)));
                    },
                    function (\Throwable $exception): bool {
                        if ($exception instanceof ConnectionException) {
                            return true;
                        }

                        if ($exception instanceof RequestException) {
                            return $exception->response->serverError()
                                || $exception->response->status() === 429;
                        }

                        return false;
                    },
                    throw: false,
                )
                ->acceptJson()
                ->post($url, $payload);
        } catch (\Throwable $exception) {
            Log::error('Gemini API request failed', [
                'method' => $method,
                'error' => $exception->getMessage(),
            ]);

            throw AiServiceException::requestFailed(
                'Gemini API request failed: '.$exception->getMessage(),
                $exception,
            );
        }

        Log::info('Gemini API response', [
            'method' => $method,
            'status' => $response->status(),
            'body' => $response->json(),
        ]);

        if ($response->failed()) {
            throw AiServiceException::requestFailed(
                'Gemini API returned an error: '.$response->body(),
            );
        }

        return $response->json();
    }

    protected function extractTextFromResponse(array $responseBody): string
    {
        $text = data_get($responseBody, 'candidates.0.content.parts.0.text');

        if (! is_string($text) || trim($text) === '') {
            throw AiServiceException::invalidResponse('Gemini response did not contain text content.');
        }

        return trim($text);
    }

    protected function validateAnalyzeResumeResponse(array $data): array
    {
        $this->assertRequiredKeys($data, [
            'ats_score',
            'strengths',
            'weaknesses',
            'missing_keywords',
            'recommendations',
            'experience_level',
            'suggested_roles',
        ]);

        $this->assertIntegerBetween($data['ats_score'], 0, 100, 'ats_score');
        $data['ats_score'] = (int) $data['ats_score'];
        $this->assertStringArray($data['strengths'], 'strengths');
        $this->assertStringArray($data['weaknesses'], 'weaknesses');
        $this->assertStringArray($data['missing_keywords'], 'missing_keywords');
        $this->assertStringArray($data['recommendations'], 'recommendations');
        $this->assertStringArray($data['suggested_roles'], 'suggested_roles');
        $this->assertEnum($data['experience_level'], ['junior', 'mid', 'senior'], 'experience_level');

        return $data;
    }

    protected function validateCoverLetterResponse(array $data): array
    {
        $this->assertRequiredKeys($data, ['cover_letter', 'subject_line', 'key_highlights']);
        $this->assertNonEmptyString($data['cover_letter'], 'cover_letter');
        $this->assertNonEmptyString($data['subject_line'], 'subject_line');
        $this->assertStringArray($data['key_highlights'], 'key_highlights');

        return $data;
    }

    protected function validateInterviewQuestionsResponse(array $data, int $count): array
    {
        $this->assertRequiredKeys($data, ['questions']);

        if (! is_array($data['questions']) || count($data['questions']) !== $count) {
            throw AiServiceException::invalidResponse("questions must contain exactly {$count} items.");
        }

        foreach ($data['questions'] as $index => $question) {
            if (! is_array($question)) {
                throw AiServiceException::invalidResponse("questions[{$index}] must be an object.");
            }

            $this->assertRequiredKeys($question, ['question', 'category', 'suggested_answer', 'tips'], "questions[{$index}]");
            $this->assertNonEmptyString($question['question'], "questions[{$index}].question");
            $this->assertEnum($question['category'], ['technical', 'behavioral', 'situational'], "questions[{$index}].category");
            $this->assertNonEmptyString($question['suggested_answer'], "questions[{$index}].suggested_answer");
            $this->assertNonEmptyString($question['tips'], "questions[{$index}].tips");
        }

        return $data;
    }

    protected function validateMatchResponse(array $data): array
    {
        $this->assertRequiredKeys($data, [
            'match_score',
            'matching_skills',
            'missing_skills',
            'recommendations',
            'suitability',
        ]);

        $this->assertIntegerBetween($data['match_score'], 0, 100, 'match_score');
        $data['match_score'] = (int) $data['match_score'];
        $this->assertStringArray($data['matching_skills'], 'matching_skills');
        $this->assertStringArray($data['missing_skills'], 'missing_skills');
        $this->assertStringArray($data['recommendations'], 'recommendations');
        $this->assertEnum($data['suitability'], ['high', 'medium', 'low'], 'suitability');

        return $data;
    }

    protected function assertRequiredKeys(array $data, array $keys, string $context = 'response'): void
    {
        foreach ($keys as $key) {
            if (! array_key_exists($key, $data)) {
                throw AiServiceException::invalidResponse("Missing required key [{$key}] in {$context}.");
            }
        }
    }

    protected function assertStringArray(mixed $value, string $field): void
    {
        if (! is_array($value) || array_filter($value, fn ($item) => ! is_string($item)) !== []) {
            throw AiServiceException::invalidResponse("{$field} must be an array of strings.");
        }
    }

    protected function assertNonEmptyString(mixed $value, string $field): void
    {
        if (! is_string($value) || trim($value) === '') {
            throw AiServiceException::invalidResponse("{$field} must be a non-empty string.");
        }
    }

    protected function assertIntegerBetween(mixed $value, int $min, int $max, string $field): void
    {
        if (! is_numeric($value) || (int) $value < $min || (int) $value > $max) {
            throw AiServiceException::invalidResponse("{$field} must be an integer between {$min} and {$max}.");
        }
    }

    protected function assertEnum(mixed $value, array $allowed, string $field): void
    {
        if (! is_string($value) || ! in_array($value, $allowed, true)) {
            throw AiServiceException::invalidResponse(
                "{$field} must be one of: ".implode(', ', $allowed).'.',
            );
        }
    }
}
