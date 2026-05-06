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
       Schema::create('membershipcards', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Liên kết với bảng users
        $table->string('card_number')->unique(); // Ví dụ: 4PM-2025-08412
        $table->string('tier')->default('Đồng'); // Đồng, Bạc, Vàng, Kim Cương
        $table->decimal('total_spent', 15, 2)->default(0); // Tổng chi tiêu
        $table->integer('points')->default(0); // Điểm tích lũy hiện tại
        $table->date('expiry_date'); // Hạn thẻ
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membershipcards');
    }
};
