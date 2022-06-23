<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShowAppointmentRequest;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Resources\ScheduleResource;
use App\Models\Appointment;
use App\Repositories\AppointmentRepository;
use App\Repositories\ScheduleRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{


    /**
     * Constructor inject the repositories
     * 
     * @param ScheduleRepository
     * @param AppointmentRepository
     */
    public function __construct(private ScheduleRepository $scheduleRepository, private AppointmentRepository $appointmentRepository)
    {
    }

    /**
     * Display all the schedules and their details
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $schedules = $this->scheduleRepository->getSchedules();
        if ($schedules->isNotEmpty()) {
            $appointments = $this->appointmentRepository->getScheduledAppointments($schedules->pluck('id')->all());
            $schedules->transform(function ($schedule) use ($appointments) {
                $appointmentsSchedule = $appointments->where('schedule_id', $schedule->id)->get();
                if ($appointmentsSchedule->isNotEmpty()) {
                    $appointmentsSchedule->transform(function ($appointment) use ($schedule) {
                        $appointment->availability = $schedule->max_client_per_slot - $appointment->booked;
                        return $appointment;
                    });
                }
                $schedule->appointments = $appointmentsSchedule;
                return $schedule;
            });
        }
        return response()->json(ScheduleResource::collection($schedules));
    }

    /**
     * Store the appointment
     *
     * @param  \App\Http\Requests\StoreAppointmentRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAppointmentRequest $request)
    {
        // Check if the schedule id is exists
        $schedule = $this->scheduleRepository->getSchedule($request->schedule_id);
        if (empty($schedule)) {
            return response()->json(['message' => trans('api.messages.appointment.schedule_not_exists')], 422);
        }

        $appointmentDateTime = Carbon::parse($request->appointment_datetime);

        // Validate the appointmentdate time. It should not be in past and not more than allowed days in future
        $this->scheduleRepository->checkValidAppointmentDateTime($schedule, $appointmentDateTime);

        // Validate the appointmentdate time. It should not be in past and not more than allowed days in future
        $this->scheduleRepository->checkPublicHoliday($schedule, $appointmentDateTime);

        // Check if the slot is valid. It is in the schedule. It is not in the breaks. 
        $validSlot = $this->scheduleRepository->checkValidSlot($schedule, $appointmentDateTime);

        if (!$validSlot) {
            return response()->json(['message' => trans('api.messages.appointment.invalid_slot')], 422);
        }

        // Book the appointment
        $appointment = $this->appointmentRepository->store($schedule, $request->all());

        return response()->json(['message' => trans('api.messages.appointment.booked')]);
    }
}
