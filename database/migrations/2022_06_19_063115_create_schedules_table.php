<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->integer('event_id');
            //$table->date('date');
            //$table->time('start_time')->nullable();
            //$table->time('end_time')->nullable();
            //$table->boolean('is_holiday')->default(false);
            $table->integer('slots_in_minutes');
            $table->integer('max_client_per_slot');
            $table->integer('cleanup_break_between_slot');
            $table->integer('slots_for_next_days');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schedules');
    }
};
