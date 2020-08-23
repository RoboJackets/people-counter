<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\Visit as VisitResource;
use Illuminate\Http\Resources\Json\JsonResource;

// phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint

class User extends JsonResource
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
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'gtid' => $this->when($this->shouldIncludeGTID($request), $this->gtid),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'visits' => VisitResource::collection($this->whenLoaded('visits')),
        ];
    }

    /**
     * Determine if GTID should be returned in the request
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    private function shouldIncludeGTID(Request $request): bool
    {
        // @phan-suppress-next-line PhanPossiblyUndeclaredMethod
        return (auth()->user()->id == $this->id || $request->user()->isSuperAdmin());
    }
}
