<?php

use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;

it('has schedules for men and woman', function () {
    $this->seed(DatabaseSeeder::class);
    $this->get('/api/v1/appointments')->assertStatus(200)
        ->assertJsonCount(2)
        ->assertJsonPath('0.eventName', 'Men Haircut')
        ->assertJsonPath('1.eventName', 'Woman Haircut');
});


it('cannot book schedule for next sunday', function () {
    $this->seed(DatabaseSeeder::class);
    $this->post('/api/v1/appointment', [
        'email' => 'ashok.gadri@gmail.com',
        'first_name' => 'Ashok',
        'last_name' => 'Gadri',
        'schedule_id' => 1,
        'appointment_datetime' => Carbon::now()->endOfWeek()->toDateTimeString()
    ])->assertStatus(422)
        ->assertJsonPath('message', 'You are trying to book slot on holiday');
});

it('cannot book schedule for 3rd day', function () {
    $this->seed(DatabaseSeeder::class);
    $this->post('/api/v1/appointment', [
        'email' => 'ashok.gadri@gmail.com',
        'first_name' => 'Ashok',
        'last_name' => 'Gadri',
        'schedule_id' => 1,
        'appointment_datetime' => Carbon::now()->addDays(2)->toDateTimeString()
    ])->assertStatus(422)
        ->assertJsonPath('message', 'You are trying to book slot on public holiday');
});


it('cannot book schedule in lunch break', function () {
    $this->seed(DatabaseSeeder::class);
    $this->post('/api/v1/appointment', [
        'email' => 'ashok.gadri@gmail.com',
        'first_name' => 'Ashok',
        'last_name' => 'Gadri',
        'schedule_id' => 1,
        'appointment_datetime' => Carbon::now()->addDays()->setTime(12, 15)->toDateTimeString()
    ])->assertStatus(422)
        ->assertJsonPath('message', 'Slot time is in break time');
});


it('cannot book before schedule', function () {
    $this->seed(DatabaseSeeder::class);
    $this->post('/api/v1/appointment', [
        'email' => 'ashok.gadri@gmail.com',
        'first_name' => 'Ashok',
        'last_name' => 'Gadri',
        'schedule_id' => 1,
        'appointment_datetime' => Carbon::now()->addDays()->setTime(07, 00)->toDateTimeString()
    ])->assertStatus(422)
        ->assertJsonPath('message', 'Slot time is not in the schedule');
});


it('cannot book before current time', function () {
    $this->seed(DatabaseSeeder::class);
    $this->post('/api/v1/appointment', [
        'email' => 'ashok.gadri@gmail.com',
        'first_name' => 'Ashok',
        'last_name' => 'Gadri',
        'schedule_id' => 1,
        'appointment_datetime' => Carbon::now()->subMinutes(5)->toDateTimeString()
    ])->assertStatus(422)
        ->assertJsonPath('message', 'Appointment is not allowed in past');
});

it('cannot book after 7 days', function () {
    $this->seed(DatabaseSeeder::class);
    $this->post('/api/v1/appointment', [
        'email' => 'ashok.gadri@gmail.com',
        'first_name' => 'Ashok',
        'last_name' => 'Gadri',
        'schedule_id' => 1,
        'appointment_datetime' => Carbon::now()->addDays(9)->setTime(11, 0)->toDateTimeString()
    ])->assertStatus(422)
        ->assertJsonPath('message', 'Appointment is not allowed in future more than 7 days');
});


it('cannot book at odd time', function () {
    $this->seed(DatabaseSeeder::class);
    $this->post('/api/v1/appointment', [
        'email' => 'ashok.gadri@gmail.com',
        'first_name' => 'Ashok',
        'last_name' => 'Gadri',
        'schedule_id' => 1,
        'appointment_datetime' => Carbon::now()->addDays(1)->setTime(11, 10)->toDateTimeString()
    ])->assertStatus(422)
        ->assertJsonPath('message', 'Slot time is invalid');
});


it('cannot overbook', function () {
    $this->seed(DatabaseSeeder::class);

    $this->post('/api/v1/appointment', [
        'email' => 'ashok.gadri@gmail.com',
        'first_name' => 'Ashok',
        'last_name' => 'Gadri',
        'schedule_id' => 1,
        'appointment_datetime' => Carbon::now()->addDays(1)->setTime(11, 15)->toDateTimeString()
    ]);

    $this->post('/api/v1/appointment', [
        'email' => 'ashok.gadri@gmail.com',
        'first_name' => 'Ashok',
        'last_name' => 'Gadri',
        'schedule_id' => 1,
        'appointment_datetime' => Carbon::now()->addDays(1)->setTime(11, 15)->toDateTimeString()
    ]);

    $this->post('/api/v1/appointment', [
        'email' => 'ashok.gadri@gmail.com',
        'first_name' => 'Ashok',
        'last_name' => 'Gadri',
        'schedule_id' => 1,
        'appointment_datetime' => Carbon::now()->addDays(1)->setTime(11, 15)->toDateTimeString()
    ]);

    $this->post('/api/v1/appointment', [
        'email' => 'ashok.gadri@gmail.com',
        'first_name' => 'Ashok',
        'last_name' => 'Gadri',
        'schedule_id' => 1,
        'appointment_datetime' => Carbon::now()->addDays(1)->setTime(11, 15)->toDateTimeString()
    ]);

    $this->post('/api/v1/appointment', [
        'email' => 'ashok.gadri@gmail.com',
        'first_name' => 'Ashok',
        'last_name' => 'Gadri',
        'schedule_id' => 1,
        'appointment_datetime' => Carbon::now()->addDays(1)->setTime(11, 15)->toDateTimeString()
    ])->assertStatus(422)
        ->assertJsonPath('message', 'Slot is already booked');
});
