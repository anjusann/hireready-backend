<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    protected function transformResource(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar
                ? Storage::disk('public')->url($this->avatar)
                : null,
            'phone' => $this->phone,
            'location' => $this->location,
            'linkedin_url' => $this->linkedin_url,
            'subscription_plan' => $this->subscription_plan?->value,
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
