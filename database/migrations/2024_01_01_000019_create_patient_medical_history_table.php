<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('PatientMedicalHistory', function (Blueprint $table) {
            $table->increments('history_id');
            $table->unsignedInteger('user_id');
            $table->string('condition', 150);
            $table->date('diagnosed_at')->nullable();
            $table->string('treated_at', 200)->nullable();
            $table->boolean('is_chronic')->default(false);
            $table->string('notes', 255)->nullable();

            $table->foreign('user_id')
                  ->references('user_id')->on('Users')
                  ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('PatientMedicalHistory');
    }
};
