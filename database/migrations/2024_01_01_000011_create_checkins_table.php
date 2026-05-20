<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('CheckIns', function (Blueprint $table) {
            $table->increments('checkin_id');
            $table->unsignedInteger('appointment_id')->unique();
            $table->dateTime('checkin_time')->useCurrent();
            $table->unsignedInteger('queue_number')->nullable();
            $table->unsignedInteger('est_wait_minutes')->nullable();
            $table->dateTime('called_at')->nullable();
            $table->string('status', 30)->default('Đang chờ');

            $table->foreign('appointment_id')
                  ->references('appointment_id')->on('Appointments')
                  ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('CheckIns');
    }
};
