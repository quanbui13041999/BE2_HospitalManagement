<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('PatientAllergies', function (Blueprint $table) {
            $table->increments('allergy_id');
            $table->unsignedInteger('user_id');
            $table->string('allergen', 150);
            $table->string('reaction', 255)->nullable();
            $table->string('severity', 30)->nullable();
            $table->date('noted_date')->useCurrent();
            $table->string('notes', 255)->nullable();

            $table->foreign('user_id')
                  ->references('user_id')->on('Users')
                  ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('PatientAllergies');
    }
};
