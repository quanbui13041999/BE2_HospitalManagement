<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('queue_counters', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('schedule_id')->unique();
            $table->unsignedBigInteger('current_ticket_id')->nullable(); // ticket đang khám
            $table->unsignedSmallInteger('last_called_number')->default(0);
            $table->timestamps();

            $table->foreign('schedule_id')->references('schedule_id')->on('doctorschedules')->onDelete('cascade');
            $table->foreign('current_ticket_id')->references('ticket_id')->on('queue_tickets')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_counters');
    }
};
