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

    public function getSchedules()
    {
        return $this->model->with(['scheduleBreaks', 'scheduledAppointments'])->orderBy('date')->get();
    }

    public function getSchedulesForDay($data)
    {
        return $this->model->with(['scheduleBreaks', 'scheduledAppointments'])->where('date', $data['date'])->get();
    }

    public function getSchedule($scheduleId)
    {
        return $this->model->where('id', $scheduleId)->first();
    }

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

    private function isValidSlot($schedule, $slotTime)
    {
        $allDaySlots = $this->getAllDaySlots($schedule);
        if (!in_array($slotTime, $allDaySlots)) {
            throw new GeneralExceptioon(trans('api.messages.appointment.not_valid_slot'));
        }

        return true;
    }

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
