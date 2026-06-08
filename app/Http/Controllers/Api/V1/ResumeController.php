<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Resume\ResumeUploadRequest;
use App\Http\Requests\Resume\UpdateResumeRequest;
use App\Http\Resources\ResumeResource;
use App\Services\ResumeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResumeController extends BaseController
{
    public function __construct(protected ResumeService $resumeService) {}

    public function upload(ResumeUploadRequest $request): JsonResponse
    {
        $result = $this->resumeService->upload(
            $request->user(),
            $request->file('file'),
            $request->validated('title'),
        );

        $meta = [];

        if ($result['warning'] !== null) {
            $meta['warning'] = $result['warning'];
        }

        return $this->successResponse(
            new ResumeResource($result['resume']),
            'Resume uploaded successfully',
            201,
            $meta,
        );
    }

    public function index(Request $request): JsonResponse
    {
        $resumes = $this->resumeService->listForUser($request->user());

        return $this->successResponse(
            ResumeResource::collection($resumes),
            'Resumes retrieved successfully',
        );
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $resume = $this->resumeService->getForUser($request->user(), $id);

        return $this->successResponse(
            new ResumeResource($resume),
            'Resume retrieved successfully',
        );
    }

    public function update(UpdateResumeRequest $request, int $id): JsonResponse
    {
        $resume = $this->resumeService->updateForUser(
            $request->user(),
            $id,
            $request->validated(),
        );

        return $this->successResponse(
            new ResumeResource($resume),
            'Resume updated successfully',
        );
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->resumeService->deleteForUser($request->user(), $id);

        return $this->successResponse(null, 'Resume deleted successfully');
    }

    public function setPrimary(Request $request, int $id): JsonResponse
    {
        $resume = $this->resumeService->setPrimaryForUser($request->user(), $id);

        return $this->successResponse(
            new ResumeResource($resume),
            'Primary resume updated successfully',
        );
    }
}
