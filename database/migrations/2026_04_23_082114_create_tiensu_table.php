<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tiensu', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id'); 

            // Nhóm máu & Thông tin cơ bản
            $table->string('blood_group', 5)->nullable(); // Đổi nhommau -> blood_group cho đồng bộ chuyên môn
            $table->string('yeuto_rh', 20)->nullable();
            $table->float('height')->nullable();
            $table->float('weight')->nullable();
            $table->float('bmi')->nullable();

            // Dị ứng
            $table->text('food_allergies')->nullable(); 
            $table->text('drug_allergies')->nullable(); 

            // Bệnh mãn tính
            $table->json('chronic_diseases')->nullable(); 
            $table->text('other_chronic_diseases')->nullable(); 

            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tiensu');
    }
};
