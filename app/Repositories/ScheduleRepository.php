<?php

namespace App\Repositories;

use App\Exceptions\GeneralExceptioon;
use App\Models\Schedule;
use Carbon\Carbon;
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
        return $this->model->with(['scheduleBreaks', 'appointments'])->orderBy('date')->get();
    }

    /**
     * 
     * Get all the schedules for the day
     */

    public function getSchedulesForDay($data)
    {
        return $this->model->with(['scheduleBreaks', 'appointments'])->where('date', $data['appointment_date'])->get();
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

    public function checkValidSlot($schedule, $slotTime)
    {
        $slotInSchedule = $this->slotInSchedule($schedule, $slotTime);
        if (!$slotInSchedule) {
            throw new GeneralExceptioon(trans('api.messages.appointment.not_in_schedule'));
        }
        $slotInBreak = $this->slotInBreakTime($schedule, $slotTime);
        if ($slotInBreak) {
            throw new GeneralExceptioon(trans('api.messages.appointment.slot_in_break'));
        }

        if ($slotInSchedule && !$slotInBreak) {
            $isValidSlot = $this->isValidSlot($schedule, $slotTime);
            if ($isValidSlot) {
                return true;
            }
        }

        return false;
    }

    /**
     * 
     * Check if the appointment slot is in between the schedule date/time
     */
    private function slotInSchedule($schedule, $slotTime)
    {
        if ($schedule->is_holiday) {
            throw new GeneralExceptioon(trans('api.messages.appointment.holiday'));
        }

        $startDateTime = Carbon::createFromTimeString($schedule->start_time);
        $endDateTime = Carbon::createFromTimeString($schedule->end_time);
        $slotDateTime = Carbon::createFromTimeString($slotTime);
        if (!$slotDateTime->between($startDateTime, $endDateTime)) {
            return false;
        }

        return true;
    }

    /**
     * 
     * Check if the appointment slot is in break time or not.
     */

    private function slotInBreakTime($schedule, $slotTime)
    {
        $scheduleBreaks = $schedule->scheduleBreaks;
        if (!empty($scheduleBreaks)) {
            foreach ($scheduleBreaks as $scheduleBreak) {
                $startDateTime = Carbon::createFromTimeString($scheduleBreak->start_time);
                $endDateTime = Carbon::createFromTimeString($scheduleBreak->end_time);
                $slotDateTime = Carbon::createFromTimeString($slotTime);
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
    private function isValidSlot($schedule, $slotTime)
    {
        $allDaySlots = $this->getAllDaySlots($schedule);
        if (!in_array($slotTime, $allDaySlots)) {
            throw new GeneralExceptioon(trans('api.messages.appointment.not_valid_slot'));
        }

        return true;
    }

    /**
     * 
     * Get all the slots for the schedule
     */

    private function getAllDaySlots($schedule)
    {
        $startDateTime = Carbon::createFromTimeString($schedule->start_time);
        $endDateTime = Carbon::createFromTimeString($schedule->end_time);
        $slots = [];
        while ($startDateTime < $endDateTime) {
            $slot = $startDateTime->toTimeString();
            //$slots[] = 
            $startDateTime->addMinutes($schedule->slots_in_minutes  + $schedule->cleanup_break_between_slot);
            if ($startDateTime <= $endDateTime && !$this->slotInBreakTime($schedule, $startDateTime->toTimeString())) {
                $slots[] = $slot;
            }
        }

        return $slots;
    }
}
