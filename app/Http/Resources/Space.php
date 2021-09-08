<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Space extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return array<string,int|string>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'max_occupancy' => $this->max_occupancy,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'active_visit_count' => $this->active_visit_count,
            'active_child_visit_count' => $this->active_child_visit_count,
            'parent' => $this->whenLoaded('parent'),
            'children' => $this->whenLoaded('children'),
            'users' => $this->whenLoaded('users'),
            'visits' => $this->whenLoaded('visits'),
            'active_visits_users' => $this->whenLoaded('activeVisitsUsers'),
            'active_child_visits_users' => $this->whenLoaded('activeChildVisitsUsers'),
        ];
    }
}
