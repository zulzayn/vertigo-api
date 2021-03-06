<?php

namespace App\Http\Resources\Calendar\EBS;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Calendar\EBS\EBS as EBSResource;

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
        if ($request->type == 'monthly') {
            $month = date('m', strtotime($request->start_date));
            $bookings =  EBSResource::collection($this->ebsMonthly($month));
        } else {
            $date = date('Y-m-d', strtotime($request->start_date . ' 00:00:00'));
            $bookings = EBSResource::collection($this->ebsDaily($date));
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => url($this->img_path),
            'tag_number' => $this->tag_number,
            'description' => $this->description,
            'category' => $this->equipmentcategory->name ?? 'N/A',
            'bookings' => $bookings,
        ];
    }
}
