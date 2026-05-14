<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bhyt_cards', function (Blueprint $table) {
            $table->increments('bhyt_card_id');
            $table->unsignedInteger('patient_id');
            $table->string('card_number', 30)->unique();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date');
            $table->unsignedInteger('coverage_rate')->default(80);
            $table->string('status', 30)->default('Còn hạn');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('patient_id')
                  ->references('user_id')->on('Users')
                  ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bhyt_cards');
    }
};
