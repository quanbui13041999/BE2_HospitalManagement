<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('VaccinationRecords', function (Blueprint $table) {
            $table->increments('vaccination_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('vaccine_id');
            $table->unsignedInteger('doctor_id')->nullable();
            $table->unsignedInteger('dose_number')->default(1);
            $table->dateTime('administered_at')->nullable();
            $table->string('batch_number', 50)->nullable();
            $table->date('next_dose_date')->nullable();
            $table->string('status', 20)->default('Chưa tiêm');
            $table->string('notes', 255)->nullable();

            $table->foreign('user_id')
                  ->references('user_id')->on('Users')
                  ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('vaccine_id')
                  ->references('vaccine_id')->on('Vaccines')
                  ->onUpdate('cascade')->onDelete('restrict');

            $table->foreign('doctor_id')
                  ->references('doctor_id')->on('Doctors')
                  ->onUpdate('cascade')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('VaccinationRecords');
    }
};
