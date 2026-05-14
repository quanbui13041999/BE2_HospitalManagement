<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->increments('appointment_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('schedule_id')->nullable();
            $table->unsignedInteger('service_id')->nullable();
            $table->dateTime('appointment_time');
            $table->dateTime('appointment_time_end')->nullable(); // Merged from 2026 migration
            $table->integer('queue_number')->nullable();
            $table->string('status', 50)->default('Chờ xác nhận');
            $table->text('note')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->dateTime('slot_hold_expire')->nullable();
            $table->unsignedInteger('rescheduled_from')->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->foreign('user_id')
                  ->references('user_id')->on('users')
                  ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
