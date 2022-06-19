<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Resources\ScheduleResource;
use App\Models\Appointment;
use App\Repositories\AppointmentRepository;
use App\Repositories\ScheduleRepository;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{

    public function __construct(private ScheduleRepository $scheduleRepository, private AppointmentRepository $appointmentRepository)
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $schedules = $this->scheduleRepository->getSchedules();
        return response()->json(ScheduleResource::collection($schedules));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreAppointmentRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAppointmentRequest $request)
    {
        $schedule = $this->scheduleRepository->getSchedule($request->schedule_id);
        if (empty($schedule)) {
            return response()->json(['message' => trans('api.messages.appointment.schedule_not_exists')], 422);
        }

        $validSlot = $this->scheduleRepository->checkValidSlot($schedule, $request->slot_time);

        if (!$validSlot) {
            return response()->json(['message' => trans('api.messages.appointment.invalid_slot')], 422);
        }

        $slotAvailable = $this->appointmentRepository->checkAvailability($schedule, $request->slot_time);
        if (!$slotAvailable) {
            return response()->json(['message' => trans('api.messages.appointment.already_booked')], 422);
        }

        $appointment = $this->appointmentRepository->store($schedule, $request->all());

        return response()->json(['message' => trans('api.messages.appointment.booked')]);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($request)
    {
        $schedules = $this->scheduleRepository->getSchedulesForDay($request->all());
        return response()->json(ScheduleResource::collection($schedules));
    }
}
