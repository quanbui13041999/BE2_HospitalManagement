<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('Doctors', function (Blueprint $table) {
            $table->increments('doctor_id');
            $table->unsignedInteger('user_id')->unique();
            $table->string('full_name', 100);
            $table->unsignedInteger('department_id');
            $table->unsignedInteger('experience')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('avatar_url', 500)->nullable();
            $table->string('bio', 1000)->nullable();
            $table->boolean('status')->default(true);

            $table->foreign('user_id')
                  ->references('user_id')->on('Users')
                  ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('department_id')
                  ->references('department_id')->on('Departments')
                  ->onUpdate('cascade')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Doctors');
    }
};
