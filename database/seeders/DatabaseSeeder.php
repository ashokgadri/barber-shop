<?php

namespace Database\Seeders;

use App\Models\DayBreak;
use App\Models\Schedule;
use App\Models\ScheduleBreak;
use App\Models\ScheduleDays;
use App\Models\ScheduleHolidays;
use App\Models\SchedulingEvent;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        DB::transaction(function ($table) {
            $lunchBreak = DayBreak::create([
                'name' => 'Lunch Break',
            ]);
            $cleaningBreak = DayBreak::create([
                'name' => 'Cleaning Break',
            ]);

            $menHairCut = SchedulingEvent::create([
                'name' => 'Men Haircut'
            ]);

            $womanHairCut = SchedulingEvent::create([
                'name' => 'Woman Haircut'
            ]);

            $menSchedule = Schedule::create([
                'event_id' => $menHairCut->id,
                'slots_in_minutes' => 10,
                'max_client_per_slot' =>  3,
                'cleanup_break_between_slot' =>  5,
                'slots_for_next_days' => 7
            ]);

            $womanSchedule = Schedule::create([
                'event_id' => $womanHairCut->id,
                'slots_in_minutes' =>  60,
                'max_client_per_slot' => 3,
                'cleanup_break_between_slot' => 10,
                'slots_for_next_days' => 7
            ]);

            ScheduleBreak::create([
                'break_id' => $lunchBreak->id,
                'schedule_id' => $menSchedule->id,
                'start_time' => '12:00',
                'end_time' => '13:00'
            ]);

            ScheduleBreak::create([
                'break_id' => $cleaningBreak->id,
                'schedule_id' => $menSchedule->id,
                'start_time' => '15:00',
                'end_time' => '16:00'
            ]);


            $days = [
                Carbon::MONDAY => ['start' => '08:00', 'end' => '20:00'],
                Carbon::TUESDAY => ['start' => '08:00', 'end' => '20:00'],
                Carbon::WEDNESDAY => ['start' => '08:00', 'end' => '20:00'],
                Carbon::THURSDAY => ['start' => '08:00', 'end' => '20:00'],
                Carbon::FRIDAY => ['start' => '08:00', 'end' => '20:00'],
                Carbon::SATURDAY => ['start' => '10:00', 'end' => '22:00'],
                Carbon::SUNDAY => ['start' => null, 'end' => null, 'holiday' => true]
            ];

            foreach ($days as $day_id => $day) {

                $holiday = $day['holiday'] ?? false;

                $startTime = $holiday ? null :   $day['start'];
                $endTime = $holiday ? null : $day['end'];

                ScheduleDays::create([
                    'schedule_id' => $menSchedule->id,
                    'int_day' => $day_id,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'is_holiday' => $holiday
                ]);

                ScheduleDays::create([
                    'schedule_id' => $womanSchedule->id,
                    'int_day' => $day_id,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'is_holiday' => $holiday
                ]);
            }
            // Get current date
            ScheduleHolidays::create([
                'schedule_id' => $menSchedule->id,
                'holiday' => Carbon::now()->addDays(2)->toDateString()
            ]);

            ScheduleHolidays::create([
                'schedule_id' => $womanSchedule->id,
                'holiday' => Carbon::now()->addDays(2)->toDateString()
            ]);
        });
    }
}
