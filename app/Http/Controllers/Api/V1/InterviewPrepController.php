<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\AI\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InterviewPrepController extends BaseController
{
    public function __construct(
        protected GeminiService $gemini
    ) {}

    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'job_role'   => ['required', 'string', 'max:255'],
            'difficulty' => ['sometimes', 'string', 'in:easy,medium,hard'],
            'count'      => ['sometimes', 'integer', 'min:5', 'max:20'],
        ]);

        $result = $this->gemini->generateInterviewQuestions(
            $request->job_role,
            $request->difficulty ?? 'medium',
            $request->count ?? 10
        );

        return $this->successResponse($result, 'Interview questions generated successfully.');
    }
}