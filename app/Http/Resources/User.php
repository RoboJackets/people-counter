<?php

namespace App\Http\Resources;

use App\Http\Resources\Visit as VisitResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<mixed>
     */
    public function toArray(Request $request)
    {
        $parent = parent::toArray($request);
        return array_merge(
            $parent,
            [
                'visits' => VisitResource::collection($this->whenLoaded('visits')),
            ]
        );
    }
}
