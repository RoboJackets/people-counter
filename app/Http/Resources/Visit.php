<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Visit extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<mixed>
     */
    public function toArray(\Illuminate\Http\Request $request)
    {
        return parent::toArray($request);
    }
}
