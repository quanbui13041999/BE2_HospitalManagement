<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->increments('service_id');
            $table->string('service_code', 30)->unique()->nullable();
            $table->string('service_name', 150);
            $table->unsignedInteger('department_id')->nullable();
            $table->string('description', 500)->nullable();
            $table->unsignedInteger('duration_minutes')->default(30);
            $table->decimal('base_price', 15, 2)->default(0);
            $table->boolean('status')->default(true);

            $table->foreign('department_id')
                  ->references('department_id')->on('departments')
                  ->onUpdate('cascade')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
