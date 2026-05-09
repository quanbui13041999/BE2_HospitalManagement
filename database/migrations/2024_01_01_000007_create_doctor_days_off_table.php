<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('DoctorDaysOff', function (Blueprint $table) {
            $table->increments('day_off_id');
            $table->unsignedInteger('doctor_id');
            $table->date('off_date');
            $table->string('reason', 255)->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->unique(['doctor_id', 'off_date'], 'UQ_DoctorDaysOff');

            $table->foreign('doctor_id')
                  ->references('doctor_id')->on('Doctors')
                  ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('DoctorDaysOff');
    }
};
