<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('MedicalRecords', function (Blueprint $table) {
            $table->increments('record_id');
            $table->string('record_code', 30)->unique()->nullable();
            $table->unsignedInteger('patient_id')->nullable();
            $table->string('patient_name', 100)->nullable();
            $table->string('patient_code', 30)->nullable();
            $table->unsignedInteger('doctor_id')->nullable();
            $table->string('doctor_name', 100)->nullable();
            $table->unsignedInteger('appointment_id')->nullable()->unique();
            $table->date('exam_date')->nullable();
            $table->time('exam_time')->nullable();
            $table->string('visit_type', 20)->default('Khám mới');
            $table->string('chief_complaint', 1000)->nullable();
            $table->string('status', 30)->default('pending');
            $table->text('status_note')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->nullable();

            $table->foreign('appointment_id')
                  ->references('appointment_id')->on('Appointments')
                  ->onUpdate('cascade')->onDelete('set null');

            $table->foreign('patient_id')
                  ->references('user_id')->on('Users')
                  ->onUpdate('cascade')->onDelete('set null');

            $table->foreign('doctor_id')
                  ->references('user_id')->on('Users')
                  ->onUpdate('cascade')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('MedicalRecords');
    }
};
