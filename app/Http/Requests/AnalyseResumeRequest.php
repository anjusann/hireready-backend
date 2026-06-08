<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnalyseResumeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'resume_id'          => ['required', 'integer', 'exists:resumes,id'],
            'job_description_id' => ['nullable', 'integer', 'exists:job_descriptions,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'resume_id.required' => 'Please select a resume to analyse.',
            'resume_id.exists'   => 'Selected resume does not exist.',
        ];
    }
}
