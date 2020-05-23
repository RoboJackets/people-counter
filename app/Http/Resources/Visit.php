<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\User as UserResource;

// phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint

class Visit extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array<mixed>
     */
    public function toArray($request)
    {
        $parent = parent::toArray($request);
        return array_merge(
            $parent,
            [
                'user' => UserResource::collection($this->whenLoaded('user')),
            ]
        );
    }
}
