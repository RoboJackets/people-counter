<?php

namespace App\Http\Resources;

use App\Http\Resources\Visit as VisitResource;
use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $parent = parent::toArray($request);
        return array_merge(
            $parent,
            [
                'visits' => VisitResource::collection($this->whenLoaded('visits'))
            ]
        );
    }
}
