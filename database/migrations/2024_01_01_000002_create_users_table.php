<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('user_id');
            $table->string('full_name', 100);
            $table->string('email', 100)->unique();
            $table->string('password', 255);
            $table->string('phone', 15)->nullable();
            $table->string('address', 255)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 10)->nullable();
            $table->unsignedInteger('role_id');
            $table->string('avatar_url', 500)->nullable();
            $table->boolean('status')->default(true);
            $table->dateTime('created_at')->useCurrent();

            $table->foreign('role_id')
                  ->references('role_id')->on('roles')
                  ->onUpdate('cascade')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
