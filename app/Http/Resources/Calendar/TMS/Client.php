<?php

namespace App\Http\Resources\Calendar\TMS;

use Illuminate\Http\Resources\Json\JsonResource;

class Client extends JsonResource
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
            'name' => $this->client_name,
            'start' => [
                'hour' => intval(date('H', strtotime($this->sitevisit_start_date))),
                'minute' => intval(date('i', strtotime($this->sitevisit_start_date))),
            ],
            'end' => [
                'hour' => intval(date('H', strtotime($this->sitevisit_end_date))),
                'minute' => intval(date('i', strtotime($this->sitevisit_end_date))),
            ],
        ];
    }
}
