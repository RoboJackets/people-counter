<?php

namespace App\Http\Resources;

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
        return parent::toArray($request);
    }
}
