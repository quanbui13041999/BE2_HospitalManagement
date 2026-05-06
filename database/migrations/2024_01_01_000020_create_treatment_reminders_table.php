<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('TreatmentReminders', function (Blueprint $table) {
            $table->increments('reminder_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('record_id')->nullable();
            $table->string('reminder_type', 50)->nullable();
            $table->dateTime('remind_at');
            $table->string('message', 500)->nullable();
            $table->boolean('is_sent')->default(false);
            $table->dateTime('created_at')->useCurrent();

            $table->foreign('user_id')
                  ->references('user_id')->on('Users')
                  ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('record_id')
                  ->references('record_id')->on('MedicalRecords')
                  ->onUpdate('cascade')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TreatmentReminders');
    }
};
