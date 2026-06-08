<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\AnalyseResumeRequest;
use App\Http\Resources\ResumeAnalysisResource;
use App\Services\ResumeAnalysisService;
use Illuminate\Http\JsonResponse;

class ResumeAnalysisController extends BaseController
{
    public function __construct(protected ResumeAnalysisService $service) {}

    public function store(AnalyseResumeRequest $request): JsonResponse
    {
        $analysis = $this->service->triggerAnalysis(
            $request->resume_id,
            $request->job_description_id
        );

        return $this->successResponse(
            new ResumeAnalysisResource($analysis),
            'Analysis started successfully.',
            202
        );
    }

    public function index(): JsonResponse
    {
        $analyses = $this->service->getUserAnalyses();

        return $this->successResponse(
            ResumeAnalysisResource::collection($analyses),
            'Analyses retrieved successfully.'
        );
    }

    public function show(int $id): JsonResponse
    {
        $analysis = $this->service->getAnalysis($id);

        return $this->successResponse(
            new ResumeAnalysisResource($analysis),
            'Analysis retrieved successfully.'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->deleteAnalysis($id);

        return $this->successResponse(null, 'Analysis deleted successfully.');
    }

    public function resumeAnalyses(int $resumeId): JsonResponse
    {
        $analyses = $this->service->getResumeAnalyses($resumeId);

        return $this->successResponse(
            ResumeAnalysisResource::collection($analyses),
            'Resume analyses retrieved successfully.'
        );
    }
}