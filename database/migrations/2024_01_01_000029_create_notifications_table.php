<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('Notifications', function (Blueprint $table) {
            $table->increments('notification_id');
            $table->unsignedInteger('user_id');
            $table->string('notif_type', 50)->nullable();
            $table->string('title', 200)->nullable();
            $table->string('content', 1000)->nullable();
            $table->unsignedInteger('ref_id')->nullable();
            $table->string('ref_type', 30)->nullable();
            $table->boolean('is_read')->default(false);
            $table->dateTime('created_at')->useCurrent();

            $table->foreign('user_id')
                  ->references('user_id')->on('Users')
                  ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Notifications');
    }
};
