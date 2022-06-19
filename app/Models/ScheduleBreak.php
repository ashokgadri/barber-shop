<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleBreak extends Model
{
    use HasFactory;

    public function dayBreak()
    {
        return $this->hasOne(DayBreak::class, 'id', 'break_id');
    }
}
