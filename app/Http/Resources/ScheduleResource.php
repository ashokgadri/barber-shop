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
            'date' => $this->date,
            'startTime' => $this->start_time,
            'endtime' => $this->end_time,
            'isHoliday' => $this->is_holiday,
            'slotsInMinutes' => $this->slots_in_minutes,
            'maxClientPerSlot' => $this->max_client_per_slot,
            'cleanupBreakBetweenSlot' => $this->cleanup_break_between_slot,
            'scheduleBreaks' => ScheduleBreakResource::collection($this->scheduleBreaks),
            'scheduledAppointments' => ScheduleAppointmentResource::collection($this->appointments)
        ];
    }
}
