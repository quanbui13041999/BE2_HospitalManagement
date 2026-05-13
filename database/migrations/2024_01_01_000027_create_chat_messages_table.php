<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ChatMessages', function (Blueprint $table) {
            $table->increments('message_id');
            $table->unsignedInteger('room_id');
            $table->unsignedInteger('sender_id');
            $table->string('message_text', 2000)->nullable();
            $table->boolean('is_read')->default(false);
            $table->dateTime('sent_at')->useCurrent();

            $table->foreign('room_id')
                  ->references('room_id')->on('ChatRooms')
                  ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('sender_id')
                  ->references('user_id')->on('Users')
                  ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ChatMessages');
    }
};
