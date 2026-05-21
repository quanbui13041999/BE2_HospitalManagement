<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ChatRooms', function (Blueprint $table) {
            $table->increments('room_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('doctor_id')->nullable();
            $table->string('status', 20)->default('Mở');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('closed_at')->nullable();

            $table->foreign('user_id')
                  ->references('user_id')->on('Users')
                  ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('doctor_id')
                  ->references('user_id')->on('Users')
                  ->onUpdate('cascade')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ChatRooms');
    }
};
