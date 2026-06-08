<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Application;
use App\Models\Resume;
use App\Models\ResumeAnalysis;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends BaseController
{
    public function stats(): JsonResponse
    {
        $userId = Auth::id();

        $totalResumes = Resume::where('user_id', $userId)->count();

        $analyses = ResumeAnalysis::where('user_id', $userId)
            ->where('status', 'completed')
            ->get();

        $averageAtsScore = $analyses->count() > 0
            ? round($analyses->avg('ats_score'))
            : 0;

        $applicationsSent = Application::where('user_id', $userId)->count();

        $interviewsScheduled = Application::where('user_id', $userId)
            ->whereIn('status', ['interview_scheduled', 'final_interview'])
            ->count();

        $atsTrend = ResumeAnalysis::where('user_id', $userId)
            ->where('status', 'completed')
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($a) => [
                'date'  => $a->created_at->format('M d'),
                'score' => $a->ats_score,
            ]);

        $statusBreakdown = Application::where('user_id', $userId)
            ->get()
            ->groupBy('status')
            ->map(fn($group, $status) => [
                'status' => $status,
                'count'  => $group->count(),
            ])
            ->values();

        return $this->successResponse([
            'total_resumes'        => $totalResumes,
            'average_ats_score'    => $averageAtsScore,
            'applications_sent'    => $applicationsSent,
            'interviews_scheduled' => $interviewsScheduled,
            'ats_trend'            => $atsTrend,
            'status_breakdown'     => $statusBreakdown,
        ], 'Dashboard stats retrieved successfully.');
    }
}