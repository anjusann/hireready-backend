<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class ResumeResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    protected function transformResource(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'file_type' => $this->file_type?->value,
            'file_size' => $this->file_size,
            'extracted_text' => $this->when(
                $request->routeIs('resumes.show'),
                $this->extracted_text,
            ),
            'has_extracted_text' => ! empty($this->extracted_text),
            'is_primary' => $this->is_primary,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
