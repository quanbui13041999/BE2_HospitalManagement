<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('device_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120)->unique();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('lock_version')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_types');
    }
};
