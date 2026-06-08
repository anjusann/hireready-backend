<?php

namespace App\Services;

use App\Jobs\AnalyseResumeJob;
use App\Models\ResumeAnalysis;
use App\Repositories\Interfaces\ResumeAnalysisRepositoryInterface;
use App\Repositories\Interfaces\ResumeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ResumeAnalysisService
{
    public function __construct(
        protected ResumeAnalysisRepositoryInterface $analysisRepo,
        protected ResumeRepositoryInterface $resumeRepo
    ) {}

    public function triggerAnalysis(int $resumeId, ?int $jobDescriptionId = null): ResumeAnalysis
    {
        $user   = Auth::user();
       $resume = $this->resumeRepo->findForUser($resumeId, $user->id);

        if (!$resume) {
            throw new HttpException(404, 'Resume not found.');
        }

        if (empty($resume->extracted_text)) {
            throw new HttpException(422, 'Resume text could not be extracted. Please re-upload.');
        }

        // Free tier limit
        if ($user->subscription_plan === 'free') {
            $todayCount = $this->analysisRepo->countTodayByUser($user->id);
            if ($todayCount >= 3) {
                throw new HttpException(429, 'Daily analysis limit reached. Upgrade to Pro for unlimited analyses.');
            }
        }

        $analysis = $this->analysisRepo->create([
            'user_id'            => $user->id,
            'resume_id'          => $resumeId,
            'job_description_id' => $jobDescriptionId,
            'status'             => 'pending',
            'ats_score'          => 0,
            'strengths'          => [],
            'weaknesses'         => [],
            'missing_keywords'   => [],
            'recommendations'    => [],
        ]);

        AnalyseResumeJob::dispatch($analysis->id, $resumeId, $jobDescriptionId);

        return $analysis;
    }

    public function getUserAnalyses(): Collection
    {
        return $this->analysisRepo->findByUser(Auth::id());
    }

    public function getAnalysis(int $id): ResumeAnalysis
    {
        $analysis = $this->analysisRepo->findByIdAndUser($id, Auth::id());

        if (!$analysis) {
            throw new HttpException(404, 'Analysis not found.');
        }

        return $analysis;
    }

    public function deleteAnalysis(int $id): bool
    {
        $this->getAnalysis($id);
        return $this->analysisRepo->delete($id);
    }

    public function getResumeAnalyses(int $resumeId): Collection
    {
        return $this->analysisRepo->findByResume($resumeId);
    }
}