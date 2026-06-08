<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'company_name'   => $this->company_name,
            'job_title'      => $this->job_title,
            'status'         => $this->status instanceof \App\Enums\ApplicationStatus
                                    ? $this->status->value
                                    : $this->status,
            'applied_date'   => $this->applied_date?->format('Y-m-d'),
            'notes'          => $this->notes,
            'follow_up_date' => $this->follow_up_date?->format('Y-m-d'),
            'created_at'     => $this->created_at,
        ];
    }
}