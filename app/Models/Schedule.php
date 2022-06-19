<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    public function event()
    {
        return $this->hasOne(SchedulingEvent::class, 'id', 'event_id');
    }

    public function scheduleBreaks()
    {
        return $this->hasMany(ScheduleBreak::class, 'schedule_id', 'id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'schedule_id', 'id');
    }
}
