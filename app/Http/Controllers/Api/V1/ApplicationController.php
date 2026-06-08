<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\StoreApplicationRequest;
use App\Http\Resources\ApplicationResource;
use App\Models\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ApplicationController extends BaseController
{
    public function index(): JsonResponse
    {
        $applications = Application::where('user_id', Auth::id())
            ->latest()
            ->get();

        return $this->successResponse(
            ApplicationResource::collection($applications),
            'Applications retrieved successfully.'
        );
    }

    public function store(StoreApplicationRequest $request): JsonResponse
    {
        $application = Application::create([
            ...$request->validated(),
            'user_id' => Auth::id(),
        ]);

        return $this->successResponse(
            new ApplicationResource($application),
            'Application created successfully.',
            201
        );
    }

    public function update(StoreApplicationRequest $request, int $id): JsonResponse
    {
        $application = Application::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $application->update($request->validated());

        return $this->successResponse(
            new ApplicationResource($application),
            'Application updated successfully.'
        );
    }

    public function updateStatus(int $id): JsonResponse
    {
        $application = Application::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $application->update(['status' => request('status')]);

        return $this->successResponse(
            new ApplicationResource($application),
            'Status updated successfully.'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        Application::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail()
            ->delete();

        return $this->successResponse(null, 'Application deleted successfully.');
    }
}