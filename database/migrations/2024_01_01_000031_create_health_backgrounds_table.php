<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_backgrounds', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->string('blood_group', 5)->nullable();
            $table->string('yeuto_rh', 20)->nullable();
            $table->double('height')->nullable();
            $table->double('weight')->nullable();
            $table->double('bmi')->nullable();
            $table->text('food_allergies')->nullable();
            $table->text('drug_allergies')->nullable();
            $table->json('chronic_diseases')->nullable();
            $table->text('other_chronic_diseases')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_backgrounds');
    }
};
