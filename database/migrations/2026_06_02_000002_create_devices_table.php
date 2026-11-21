<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('code', 50)->unique();
            $table->foreignId('device_type_id')
                ->constrained('device_types')
                ->restrictOnDelete();
            $table->enum('status', ['active', 'broken', 'maintenance'])->default('active');
            $table->date('purchase_date')->nullable();
            $table->unsignedBigInteger('lock_version')->default(1);
            $table->timestamps();

            $table->index(['device_type_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
