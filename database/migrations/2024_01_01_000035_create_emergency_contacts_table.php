<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emergency_contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->unsignedTinyInteger('priority')->default(1);
            $table->string('name');
            $table->string('relationship')->nullable();
            $table->string('phone', 20);
            $table->string('email')->nullable();
            $table->tinyInteger('lab_notifications')->default(0);
            $table->tinyInteger('recovery_updates')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->unique(['user_id', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergency_contacts');
    }
};
