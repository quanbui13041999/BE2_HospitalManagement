<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disease_nutrition_rules', function (Blueprint $table) {
            $table->increments('rule_id');
            $table->string('disease_name', 200)->nullable()->comment('Khớp diagnoses.diagnosis_name');
            $table->string('icd_code', 20)->nullable()->comment('Khớp diagnoses.icd_code');
            $table->unsignedInteger('food_id');
            $table->foreign('food_id')->references('food_id')->on('foods')->onDelete('cascade');
            $table->enum('recommendation_type', ['should_eat', 'should_avoid']);
            $table->text('reason')->nullable();
            $table->timestamps();
            $table->unique(['disease_name', 'food_id'], 'uq_disease_food');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disease_nutrition_rules');
    }
};
