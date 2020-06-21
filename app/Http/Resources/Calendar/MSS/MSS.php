<?php

namespace App\Http\Resources\Calendar\MSS;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Calendar\User as UserResource;
use App\User;

class MSS extends JsonResource
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
            'start_time' => [
                'hour' => intval(date('H', strtotime($this->start_date))),
                'minute' => intval(date('i', strtotime($this->start_date))),
            ],
            'end_time' => [
                'hour' => intval(date('H', strtotime($this->end_date))),
                'minute' => intval(date('i', strtotime($this->end_date))),
            ],
            'description' => $this->description,
            'status' => $this->status,
            'person_in_charge' => UserResource::collection($this->personInCharge),
        ];
    }
}