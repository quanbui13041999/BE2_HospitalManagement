<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('chatmessages', function (Blueprint $table) {
            $table->increments('message_id');
            $table->unsignedInteger('room_id');
            $table->unsignedInteger('sender_id')->nullable();
            $table->text('message_text');
            $table->boolean('is_read')->default(false);
            $table->boolean('is_ai')->default(false); // Merged from 2026 migration
            $table->dateTime('sent_at')->useCurrent();

            $table->foreign('room_id')->references('room_id')->on('chatrooms')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatmessages');
    }
};
