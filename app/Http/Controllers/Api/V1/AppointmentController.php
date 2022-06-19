<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShowAppointmentRequest;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Resources\ScheduleResource;
use App\Models\Appointment;
use App\Repositories\AppointmentRepository;
use App\Repositories\ScheduleRepository;
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

        // Check if the slot is valid. It is in the schedule. It is not in the breaks. 
        $validSlot = $this->scheduleRepository->checkValidSlot($schedule, $request->slot_time);

        if (!$validSlot) {
            return response()->json(['message' => trans('api.messages.appointment.invalid_slot')], 422);
        }

        // Check the availability of the slot. Is is it already booked or available
        $slotAvailable = $this->appointmentRepository->checkAvailability($schedule, $request->slot_time);
        if (!$slotAvailable) {
            return response()->json(['message' => trans('api.messages.appointment.already_booked')], 422);
        }

        // Book the appointment
        $appointment = $this->appointmentRepository->store($schedule, $request->all());

        return response()->json(['message' => trans('api.messages.appointment.booked')]);
    }

    /**
     * Display the schedule for a single day
     *
     * @return \Illuminate\Http\Response
     */
    public function show(ShowAppointmentRequest $request)
    {
        //Display the schedule for a single day
        $schedules = $this->scheduleRepository->getSchedulesForDay($request->all());
        return response()->json(ScheduleResource::collection($schedules));
    }
}
