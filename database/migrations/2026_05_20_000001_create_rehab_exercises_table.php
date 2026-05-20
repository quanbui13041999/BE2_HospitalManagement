<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rehab_exercises', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('content');
            $table->string('category', 80);
            $table->string('phase', 40);
            $table->string('thumbnail')->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['category', 'status']);
            $table->foreign('created_by')->references('user_id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rehab_exercises');
    }
};
