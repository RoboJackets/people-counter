<?php

namespace App\Http\Resources;

use App\Http\Resources\Space as SpaceResource;
use App\Http\Resources\User as UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

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
                'user' => new UserResource($this->whenLoaded('user')),
                'spaces' => new SpaceResource($this->whenLoaded('spaces')),
            ]
        );
    }
}
