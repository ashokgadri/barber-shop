<?php

namespace App\Repositories;

use App\Exceptions\GeneralExceptioon;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PDO;

class ScheduleRepository
{

    public function __construct(private Schedule $model)
    {
    }

    /**
     * 
     * Get all the schedules. I have't included any limiting to it for now. We can limit it later on based on requirement
     */

    public function getSchedules()
    {

        //->where('slot_date', '<=', DB::raw(Carbon::now()->toDateString() . ' + schedules.slots_for_next_days'))
        //->where('holiday', '<=', DB::raw(Carbon::now()->toDateString() . ' + schedules.slots_for_next_days'))
        return Cache::remember('schedules', 5 * 60, function () {
            return $this->model->with(['scheduleBreaks', 'appointments' => function ($query) {
                $query->where('slot_date', '>=', Carbon::now()->toDateString())
                    ->groupBy('slot_date')->groupBy('slot_time')->select('slot_date', 'slot_time');
            }, 'days', 'holidays' => function ($query) {
                $query->where('holiday', '>=', Carbon::now()->toDateString());
            }])->get();
        });
    }

    /**
     * 
     * Get single schedule detail based on id
     */

    public function getSchedule($scheduleId)
    {
        return $this->model->where('id', $scheduleId)->first();
    }

    /**
     * 
     * It will verify the appointment slot is valid or not
     */

    public function checkValidSlot($schedule, $appointmentDateTime)
    {
        $slotInSchedule = $this->slotInSchedule($schedule, $appointmentDateTime);
        if (!$slotInSchedule) {
            throw new GeneralExceptioon(trans('api.messages.appointment.not_in_schedule'));
        }
        $slotInBreak = $this->slotInBreakTime($schedule, $appointmentDateTime);
        if ($slotInBreak) {
            throw new GeneralExceptioon(trans('api.messages.appointment.slot_in_break'));
        }

        $isValidSlot = $this->isValidSlot($schedule, $appointmentDateTime);
        if ($isValidSlot) {
            return true;
        }

        return false;
    }

    /**
     * 
     * Check if the appointment slot is in between the schedule date/time
     */
    private function slotInSchedule($schedule, $appointmentDateTime)
    {
        $day = $schedule->days->where('int_day', $appointmentDateTime->dayOfWeek)->first();
        if ($day->is_holiday) {
            throw new GeneralExceptioon(trans('api.messages.appointment.holiday'));
        }
        $startDateTime = Carbon::createFromTimeString($day->start_time);
        $endDateTime = Carbon::createFromTimeString($day->end_time);
        $slotDateTime = Carbon::createFromTimeString($appointmentDateTime->toTimeString());
        if (!$slotDateTime->between($startDateTime, $endDateTime)) {
            return false;
        }

        return true;
    }

    /**
     * 
     * Check if the appointment slot is in break time or not.
     */

    private function slotInBreakTime($schedule, $appointmentDateTime)
    {
        $scheduleBreaks = $schedule->scheduleBreaks;
        if (!empty($scheduleBreaks)) {
            foreach ($scheduleBreaks as $scheduleBreak) {
                $startDateTime = Carbon::createFromTimeString($scheduleBreak->start_time);
                $endDateTime = Carbon::createFromTimeString($scheduleBreak->end_time);
                $slotDateTime = Carbon::createFromTimeString($appointmentDateTime->toTimeString());
                if ($slotDateTime->between($startDateTime, $endDateTime) && $startDateTime != $slotDateTime) {
                    return true;
                }
            }
        }

        return false;
    }


    /**
     * Check if it is valid slot. It will check if slot time is not random while 
     * it should be based on the slot in minutes and break time between slots
     */
    private function isValidSlot($schedule, $appointmentDateTime)
    {
        $allDaySlots = $this->getAllDaySlots($schedule, $appointmentDateTime);
        if (!in_array($appointmentDateTime->toTimeString(), $allDaySlots)) {
            throw new GeneralExceptioon(trans('api.messages.appointment.not_valid_slot'));
        }

        return true;
    }

    /**
     * 
     * Get all the slots for the schedule
     */

    private function getAllDaySlots($schedule, $appointmentDateTime)
    {
        $day = $schedule->days->where('int_day', $appointmentDateTime->dayOfWeek)->first();
        $startDateTime = Carbon::createFromTimeString($day->start_time);
        $endDateTime = Carbon::createFromTimeString($day->end_time);
        $slots = [];
        while ($startDateTime < $endDateTime) {
            $slot = $startDateTime->toTimeString();
            //$slots[] = 
            $startDateTime->addMinutes($schedule->slots_in_minutes  + $schedule->cleanup_break_between_slot);
            if ($startDateTime <= $endDateTime && !$this->slotInBreakTime($schedule, $appointmentDateTime)) {
                $slots[] = $slot;
            }
        }

        return $slots;
    }

    public function checkValidAppointmentDateTime($schedule, $appointmentDateTime)
    {
        if ($appointmentDateTime < Carbon::now()) {
            throw new GeneralExceptioon(trans('api.messages.appointment.appointment_in_past'));
        }

        if ($appointmentDateTime->diffInDays(Carbon::now()) > $schedule->slots_for_next_days) {
            throw new GeneralExceptioon(trans('api.messages.appointment.appointment_in_future', ['days' => $schedule->slots_for_next_days]));
        }
    }

    public function checkPublicHoliday($schedule, $appointmentDateTime)
    {
        $publicHoliday = $schedule->holidays->where('holiday', $appointmentDateTime->toDatestring())->first();
        if ($publicHoliday) {
            throw new GeneralExceptioon(trans('api.messages.appointment.public_holiday'));
        }
    }
}
