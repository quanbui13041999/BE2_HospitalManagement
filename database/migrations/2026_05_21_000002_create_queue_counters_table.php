<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('queue_counters')) {
            return;
        }

        Schema::create('queue_counters', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('schedule_id')->unique();
            $table->unsignedBigInteger('current_ticket_id')->nullable(); // ticket đang khám
            $table->unsignedSmallInteger('last_called_number')->default(0);
            $table->timestamps();

            $table->index('current_ticket_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_counters');
    }
};
