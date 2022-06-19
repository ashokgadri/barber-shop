<?php

it('has appointment page', function () {
    $response = $this->get('/api/v1/appointments');

    $response->assertStatus(200);
});


it('has booking page', function () {
    $response = $this->post('/api/v1/appointment', [
        'email' => 'ashok.gadri@gmail.com',
        'first_name' => 'Ashok',
        'last_name' => 'Gadri',
        'schedule_id' => 3,
        'slot_time' => '10:45:00'
    ]);

    $response->assertStatus(200);
});


it('booking in lunch time', function () {
    $response = $this->post('/api/v1/appointment', [
        'email' => 'ashok.gadri@gmail.com',
        'first_name' => 'Ashok',
        'last_name' => 'Gadri',
        'schedule_id' => 3,
        'slot_time' => '12:15:00'
    ]);

    $response->assertStatus(422);
});

it('booking on holiday', function () {
    $response = $this->post('/api/v1/appointment', [
        'email' => 'ashok.gadri@gmail.com',
        'first_name' => 'Ashok',
        'last_name' => 'Gadri',
        'schedule_id' => 1,
        'slot_time' => '12:15:00'
    ]);

    $response->assertStatus(422);
});
