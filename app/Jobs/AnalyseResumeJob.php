<?php

namespace App\Jobs;

use App\Exceptions\AiServiceException;
use App\Models\ResumeAnalysis;
use App\Services\AI\GeminiService;
use App\Repositories\ResumeRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AnalyseResumeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(
        public readonly int $resumeAnalysisId,
        public readonly int $resumeId,
        public readonly ?int $jobDescriptionId = null
    ) {}

    public function handle(GeminiService $gemini, ResumeRepository $resumeRepo): void
    {
        $analysis = ResumeAnalysis::findOrFail($this->resumeAnalysisId);
        $resume   = $resumeRepo->findOrFail($this->resumeId);

        try {
            $result = $gemini->analyzeResume($resume->extracted_text ?? '');

            $analysis->update([
                'ats_score'        => $result['ats_score'] ?? 0,
                'strengths'        => $result['strengths'] ?? [],
                'weaknesses'       => $result['weaknesses'] ?? [],
                'missing_keywords' => $result['missing_keywords'] ?? [],
                'recommendations'  => $result['recommendations'] ?? [],
                'raw_ai_response'  => json_encode($result),
                'status'           => 'completed',
                'analyzed_at'      => now(),
            ]);

        } catch (AiServiceException $e) {
            $analysis->update(['status' => 'failed']);
            Log::error('ATS analysis failed', [
                'analysis_id' => $this->resumeAnalysisId,
                'error'       => $e->getMessage(),
            ]);
            $this->fail($e);
        }
    }

    public function failed(\Throwable $e): void
    {
        ResumeAnalysis::where('id', $this->resumeAnalysisId)
            ->update(['status' => 'failed']);
    }
}