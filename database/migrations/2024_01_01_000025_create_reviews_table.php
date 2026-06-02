<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->increments('review_id');
            $table->unsignedInteger('appointment_id')->unique();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('doctor_id');
            $table->unsignedTinyInteger('rating')->nullable();
            $table->string('comment', 500)->nullable();
            $table->string('doctor_reply', 500)->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->foreign('appointment_id')
                  ->references('appointment_id')->on('appointments')
                  ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('user_id')
                  ->references('user_id')->on('users')
                  ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('doctor_id')
                  ->references('doctor_id')->on('doctors')
                  ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};