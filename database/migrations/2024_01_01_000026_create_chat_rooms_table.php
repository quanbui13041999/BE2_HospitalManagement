<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('chatrooms', function (Blueprint $table) {
            $table->increments('room_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('doctor_id');
            $table->string('status', 20)->default('Mở');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('closed_at')->nullable();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('doctor_id')->references('doctor_id')->on('doctors')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatrooms');
    }
};
