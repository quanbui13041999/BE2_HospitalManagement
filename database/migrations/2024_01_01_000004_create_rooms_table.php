<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('Rooms', function (Blueprint $table) {
            $table->increments('room_id');
            $table->string('room_code', 20)->unique();
            $table->string('room_name', 100)->nullable();
            $table->unsignedInteger('department_id')->nullable();
            $table->string('room_type', 50);
            $table->string('status', 30)->default('Trống');
            $table->string('notes', 255)->nullable();

            $table->foreign('department_id')
                  ->references('department_id')->on('Departments')
                  ->onUpdate('cascade')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Rooms');
    }
};
