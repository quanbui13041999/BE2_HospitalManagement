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
        if (Schema::hasTable('emergency_contacts')) {
            return;
        }

        Schema::create('emergency_contacts', function (Blueprint $table) {
            $table->id();
 
            // Liên kết với bảng users (bệnh nhân)
            $table->unsignedInteger('user_id');
 
            // Thứ tự ưu tiên: 1, 2, 3
            $table->unsignedTinyInteger('priority')->default(1);
 
            $table->string('name');
            $table->string('relationship')->nullable();
            $table->string('phone', 20);
            $table->string('email')->nullable();
 
            // Tùy chọn thông báo
            $table->boolean('lab_notifications')->default(false);
            $table->boolean('recovery_updates')->default(false);
 
            $table->timestamps();
            $table->softDeletes();
 
            // Mỗi user chỉ có tối đa 3 liên hệ với priority khác nhau
            $table->unique(['user_id', 'priority']);
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emergency_contacts');
    }
};
