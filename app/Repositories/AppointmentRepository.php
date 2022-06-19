<?php

namespace App\Repositories;

use App\Models\Appointment;

class AppointmentRepository
{

    const MODEL = Appointment::class;

    public function __construct(private Appointment $model)
    {
    }

    /**
     * Check the availability of the slot
     * Get all the schedule for the slot time and compare with the max allowed client per slot
     */

    public function checkAvailability($schedule, $slotTime)
    {
        $scheduledAppointmentsInSlot = $this->getScheduledAppointmentInSlot($schedule, $slotTime);
        if ($scheduledAppointmentsInSlot->count() < $schedule->max_client_per_slot) {
            return true;
        }
        return false;
    }

    /**
     * 
     * Get all the appointment at particular slot
     */

    private function getScheduledAppointmentInSlot($schedule, $slotTime)
    {
        return $this->model->where('schedule_id', $schedule->id)->where('slot_time', $slotTime)->get();
    }

    /**
     * 
     * Store the appointment
     */

    public function store($schedule, $data)
    {
        $appointment = self::MODEL;
        $appointment = new $appointment();
        $appointment->email = $data['email'];
        $appointment->first_name = $data['first_name'];
        $appointment->last_name = $data['last_name'];
        $appointment->slot_date = $schedule->date;
        $appointment->slot_time = $data['slot_time'];
        $appointment->schedule_id = $schedule->id;
        $appointment->save();
        return $appointment;
    }
}
