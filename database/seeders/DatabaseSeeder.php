<?php

namespace Database\Seeders;

use App\Models\DayBreak;
use App\Models\Schedule;
use App\Models\ScheduleBreak;
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

            // Get current date
            $date = Carbon::now();

            // Loop through seven days
            for ($i = 0; $i < 7; $i++) {
                $timings =  match ($date->weekday()) {
                    Carbon::MONDAY => ['start' => '08:00', 'end' => '20:00'],
                    Carbon::TUESDAY => ['start' => '08:00', 'end' => '20:00'],
                    Carbon::WEDNESDAY => ['start' => '08:00', 'end' => '20:00'],
                    Carbon::THURSDAY => ['start' => '08:00', 'end' => '20:00'],
                    Carbon::FRIDAY => ['start' => '08:00', 'end' => '20:00'],
                    Carbon::SATURDAY => ['start' => '10:00', 'end' => '22:00'],
                    Carbon::SUNDAY => ['start' => null, 'end' => null, 'holiday' => true],
                };

                $holiday = $timings['holiday'] ?? false;
                if ($i == 2) {
                    $holiday = true;
                }

                $startTime = $holiday ? null :   $timings['start'];
                $endTime = $holiday ? null : $timings['end'];


                $menSchedule = Schedule::create([
                    'event_id' => $menHairCut->id,
                    'date' => $date->toDate(),
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'is_holiday' => $holiday,
                    'slots_in_minutes' => $holiday ? null :  10,
                    'max_client_per_slot' => $holiday ? null :  3,
                    'cleanup_break_between_slot' => $holiday ? null :  5
                ]);

                if (!$holiday) {
                    ScheduleBreak::create([
                        'break_id' => $lunchBreak->id,
                        'schedule_id' => $menSchedule->id,
                        'date' => $date->toDate(),
                        'start_time' => '12:00',
                        'end_time' => '13:00'
                    ]);

                    ScheduleBreak::create([
                        'break_id' => $cleaningBreak->id,
                        'schedule_id' => $menSchedule->id,
                        'date' => $date->toDate(),
                        'start_time' => '15:00',
                        'end_time' => '16:00'
                    ]);
                }



                $womanSchedule = Schedule::create([
                    'event_id' => $womanHairCut->id,
                    'date' => $date->toDate(),
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'is_holiday' => $holiday,
                    'slots_in_minutes' => $holiday ? null :  60,
                    'max_client_per_slot' => $holiday ? null : 3,
                    'cleanup_break_between_slot' => $holiday ? null : 10
                ]);

                if (!$holiday) {
                    ScheduleBreak::create([
                        'break_id' => $lunchBreak->id,
                        'schedule_id' => $womanSchedule->id,
                        'date' => $date->toDate(),
                        'start_time' => '12:00',
                        'end_time' => '13:00'
                    ]);

                    ScheduleBreak::create([
                        'break_id' => $cleaningBreak->id,
                        'schedule_id' => $womanSchedule->id,
                        'date' => $date->toDate(),
                        'start_time' => '15:00',
                        'end_time' => '16:00'
                    ]);
                }
                $date->addDay();
            }
        });
    }
}
