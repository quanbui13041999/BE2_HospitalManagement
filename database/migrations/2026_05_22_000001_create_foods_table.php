<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('foods')) {
            return;
        }

        Schema::create('foods', function (Blueprint $table) {
            $table->increments('food_id');
            $table->string('food_name', 150)->unique();
            $table->unsignedSmallInteger('calories_per_100g');
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1=active, 0=hidden');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foods');
    }
};
