<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('doctor_schedules', function (Blueprint $table) {
            $table->increments('schedule_id');
            $table->unsignedInteger('doctor_id');
            $table->unsignedInteger('room_id')->nullable();
            $table->date('work_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('slot_duration')->default(30);
            $table->unsignedInteger('max_slot')->nullable();
            $table->string('status', 30)->default('Hoạt động');
            $table->string('note', 255)->nullable();

            $table->unique(['doctor_id', 'work_date', 'start_time'], 'UQ_DoctorSchedules');

            $table->foreign('doctor_id')
                  ->references('user_id')->on('Users')
                  ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('room_id')
                  ->references('room_id')->on('Rooms')
                  ->onUpdate('cascade')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('DoctorSchedules');
    }
};
