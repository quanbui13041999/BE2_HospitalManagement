<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('Appointments', function (Blueprint $table) {
            $table->increments('appointment_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('schedule_id');
            $table->unsignedInteger('service_id')->nullable();
            $table->dateTime('appointment_time')->nullable();
            $table->unsignedInteger('queue_number')->nullable();
            $table->string('status', 50)->default('Chờ xác nhận');
            $table->string('note', 255)->nullable();
            $table->string('cancel_reason', 255)->nullable();
            $table->dateTime('slot_hold_expire')->nullable();
            $table->unsignedInteger('rescheduled_from')->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->unique(['user_id', 'schedule_id'], 'UQ_Appointments_UserSchedule');

            $table->foreign('user_id')
                  ->references('user_id')->on('Users')
                  ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('schedule_id')
                  ->references('schedule_id')->on('doctor_schedules')
                  ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('service_id')
                  ->references('service_id')->on('Services')
                  ->onUpdate('cascade')->onDelete('set null');

            $table->foreign('rescheduled_from')
                  ->references('appointment_id')->on('Appointments')
                  ->onUpdate('cascade')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Appointments');
    }
};
