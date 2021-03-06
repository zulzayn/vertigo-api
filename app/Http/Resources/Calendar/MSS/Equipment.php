<?php

namespace App\Http\Resources\Calendar\MSS;

use Illuminate\Http\Resources\Json\JsonResource;

class Equipment extends JsonResource
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
            'id' => $this->equipment->id,
            'name' => $this->equipment->name,
        ];
    }
}
