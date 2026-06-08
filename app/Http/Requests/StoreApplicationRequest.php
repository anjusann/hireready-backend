<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name'   => ['required', 'string', 'max:255'],
            'job_title'      => ['required', 'string', 'max:255'],
            'status'         => ['sometimes', 'string', 'in:applied,under_review,interview_scheduled,final_interview,offer_received,rejected,withdrawn'],
            'applied_date'   => ['sometimes', 'date'],
            'notes'          => ['nullable', 'string'],
            'follow_up_date' => ['nullable', 'date'],
        ];
    }
}