<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\AI\GeminiService;
use App\Repositories\ResumeRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CoverLetterController extends BaseController
{
    public function __construct(
        protected GeminiService $gemini,
        protected ResumeRepository $resumeRepo
    ) {}

    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'resume_id'       => ['required', 'integer', 'exists:resumes,id'],
            'job_description' => ['required', 'string', 'min:50'],
            'tone'            => ['sometimes', 'string', 'in:professional,creative,concise'],
        ]);

        $resume = $this->resumeRepo->findForUser(
            $request->resume_id,
            Auth::id()
        );

        if (!$resume) {
            return $this->errorResponse('Resume not found.', 404);
        }

        if (empty($resume->extracted_text)) {
            return $this->errorResponse('Resume text could not be extracted. Please re-upload.', 422);
        }

        $result = $this->gemini->generateCoverLetter(
            $resume->extracted_text,
            $request->job_description,
            $request->tone ?? 'professional'
        );

        return $this->successResponse($result, 'Cover letter generated successfully.');
    }
}