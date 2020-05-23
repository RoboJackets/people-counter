<?php

namespace App\Http\Resources;

use App\Http\Resources\Visit as VisitResource;
use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array
     */
    public function toArray(\Illuminate\Http\Request $request)
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
