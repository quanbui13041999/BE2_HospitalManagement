<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('Vaccines', function (Blueprint $table) {
            $table->increments('vaccine_id');
            $table->string('vaccine_name', 150);
            $table->string('description', 500)->nullable();
            $table->string('manufacturer', 100)->nullable();
            $table->unsignedInteger('doses_required')->default(1);
            $table->boolean('status')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Vaccines');
    }
};
