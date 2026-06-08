<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResumeAnalysisResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'resume_id'        => $this->resume_id,
            'status'           => $this->status,
            'ats_score'        => $this->ats_score,
            'strengths'        => $this->strengths ?? [],
            'weaknesses'       => $this->weaknesses ?? [],
            'missing_keywords' => $this->missing_keywords ?? [],
            'recommendations'  => $this->recommendations ?? [],
            'analyzed_at'      => $this->analyzed_at,
            'created_at'       => $this->created_at,
            'resume'           => $this->whenLoaded('resume', fn() => [
                'id'    => $this->resume->id,
                'title' => $this->resume->title,
            ]),
        ];
    }
}