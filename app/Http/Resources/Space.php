<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Space extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'max_occupancy' => $this->max_occupancy,
            'activeVisitCount' => $this->activeVisitCount,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'parent' => $this->whenLoaded('parent'),
            'children' => $this->whenLoaded('children'),
            'users' => $this->whenLoaded('users')
        ];
    }
}
