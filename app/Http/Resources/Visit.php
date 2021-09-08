<?php

declare(strict_types=1);

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
     * @param  \Illuminate\Http\Request  $request
     *
     * @return array<mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'gtid' => $this->gtid,
            'in_door' => $this->in_door,
            'in_time' => $this->in_time,
            'out_door' => $this->out_door,
            'out_time' => $this->out_time,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'user' => new UserResource($this->whenLoaded('user')),
            'spaces' => new SpaceResource($this->whenLoaded('spaces')),
        ];
    }
}
