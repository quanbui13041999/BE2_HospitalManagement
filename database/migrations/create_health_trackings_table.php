<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('health_trackings')) {
            return;
        }

        Schema::create('health_trackings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('patient_id');
            $table->integer('systolic');
            $table->integer('diastolic');
            $table->integer('heart_rate');
            $table->integer('spo2');
            $table->decimal('weight', 5, 2);
            $table->integer('blood_sugar');
            $table->text('symptoms')->nullable();
            $table->enum('risk_level', ['normal', 'warning', 'danger'])->default('normal');
            $table->json('risk_warnings')->nullable();
            $table->unsignedBigInteger('version')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_id')
                ->references('user_id')->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_trackings');
    }
};
