<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'eventName' => $this->event->name,
            'days' => ScheduleDayResource::collection($this->days),
            'holidays' => ScheduleHolidayResource::collection($this->holidays),
            'slotsInMinutes' => $this->slots_in_minutes,
            'maxClientPerSlot' => $this->max_client_per_slot,
            'cleanupBreakBetweenSlot' => $this->cleanup_break_between_slot,
            'breaks' => ScheduleBreakResource::collection($this->scheduleBreaks),
            'appointments' => ScheduleAppointmentResource::collection($this->appointments)
        ];
    }
}
