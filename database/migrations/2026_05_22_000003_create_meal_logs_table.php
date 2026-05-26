<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meal_logs', function (Blueprint $table) {
            $table->increments('log_id');
            // FK → users.id (int UNSIGNED khớp DB hiện tại)
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // FK → foods.food_id
            $table->unsignedInteger('food_id');
            $table->foreign('food_id')->references('food_id')->on('foods')->onDelete('cascade');
            $table->enum('meal_type', ['breakfast', 'lunch', 'dinner', 'snack']);
            $table->unsignedSmallInteger('weight_gram')->comment('Gram thực phẩm đã ăn');
            $table->unsignedSmallInteger('total_calories_intake')->comment('= calories_per_100g * weight_gram / 100');
            $table->date('logged_date');
            $table->timestamps();
            $table->index(['user_id', 'logged_date'], 'idx_user_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_logs');
    }
};
