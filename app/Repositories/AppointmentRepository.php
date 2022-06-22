<?php

namespace App\Repositories;

use App\Exceptions\GeneralExceptioon;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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

    private function checkAvailability($schedule, $appointmentDatetime)
    {
        $scheduledAppointmentsInSlot = $this->getScheduledAppointmentInSlot($schedule, $appointmentDatetime);
        if ($scheduledAppointmentsInSlot->count() < $schedule->max_client_per_slot) {
            return true;
        }
        return false;
    }

    /**
     * 
     * Get all the appointment at particular slot
     */

    private function getScheduledAppointmentInSlot($schedule, $appointmentDatetime)
    {
        return $this->model->where('schedule_id', $schedule->id)->where('slot_date', $appointmentDatetime->toDateString())->where('slot_time', $appointmentDatetime->toTimeString())->lockForUpdate()->get();
    }

    /**
     * 
     * Store the appointment
     */

    public function store($schedule, $data)
    {



        DB::transaction(function () use ($data, $schedule) {

            $appointmentDatetime = Carbon::parse($data['appointment_datetime']);
            // Check the availability of the slot. Is is it already booked or available
            $slotAvailable = $this->checkAvailability($schedule, $appointmentDatetime);
            if (!$slotAvailable) {
                //return response()->json(['message' => trans('api.messages.appointment.already_booked')], 422);
                throw new GeneralExceptioon(trans('api.messages.appointment.already_booked'));
            }

            $appointment = self::MODEL;
            $appointment = new $appointment();
            $appointment->email = $data['email'];
            $appointment->first_name = $data['first_name'];
            $appointment->last_name = $data['last_name'];
            $appointment->slot_date = $appointmentDatetime->toDateString();
            $appointment->slot_time = $appointmentDatetime->toTimeString();
            $appointment->schedule_id = $schedule->id;
            $appointment->save();
            return $appointment;
        });
    }
}
