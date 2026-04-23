<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('MedicalRecords', function (Blueprint $table) {
            $table->increments('record_id');
            $table->unsignedInteger('appointment_id')->unique();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('doctor_id')->nullable();
            $table->string('diagnosis', 500)->nullable();
            $table->string('prescription', 1000)->nullable();
            $table->date('follow_up_date')->nullable();
            $table->string('file_path', 500)->nullable();
            $table->string('notes', 500)->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->foreign('appointment_id')
                  ->references('appointment_id')->on('Appointments')
                  ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('user_id')
                  ->references('user_id')->on('Users')
                  ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('doctor_id')
                  ->references('doctor_id')->on('Doctors')
                  ->onUpdate('cascade')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('MedicalRecords');
    }
};
