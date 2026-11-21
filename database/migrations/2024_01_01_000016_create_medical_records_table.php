<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('medical_records', function (Blueprint $table) {
            $table->increments('record_id');
            $table->string('record_code', 20)->unique()->nullable();
            $table->unsignedInteger('patient_id');
            $table->string('patient_name', 100)->nullable();
            $table->string('patient_code', 20)->nullable();
            $table->unsignedInteger('doctor_id')->nullable();
            $table->string('doctor_name', 100)->nullable();
            $table->unsignedInteger('appointment_id')->nullable();
            $table->date('exam_date')->nullable();
            $table->time('exam_time')->nullable();
            $table->string('visit_type', 50)->nullable();
            $table->text('chief_complaint')->nullable();
            $table->string('status', 50)->default('pending');
            $table->text('status_note')->nullable();
            $table->timestamps();

            $table->foreign('patient_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('doctor_id')->references('user_id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
