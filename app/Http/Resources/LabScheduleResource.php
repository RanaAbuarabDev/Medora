<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LabScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
     public function toArray(Request $request): array
    {
        $days = [
            0 => 'الأحد',
            1 => 'الاثنين',
            2 => 'الثلاثاء',
            3 => 'الأربعاء',
            4 => 'الخميس',
            5 => 'الجمعة',
            6 => 'السبت',
        ];

        return [
            'id'            => $this->id,
            'day_of_week'   => $this->day_of_week,
            'day_name'      => $days[$this->day_of_week],
            'start_time'    => $this->start_time,
            'end_time'      => $this->end_time,
            'slot_interval' => $this->slot_interval,
            'max_parallel'  => $this->max_parallel,
            'is_day_off'    => $this->is_day_off,
        ];
    }
}
