<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class BaseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->transformResource($request);
    }

    /**
     * Define the resource transformation logic in child classes.
     *
     * @return array<string, mixed>
     */
    abstract protected function transformResource(Request $request): array;
}
