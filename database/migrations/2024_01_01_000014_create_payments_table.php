<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->increments('payment_id');
            $table->unsignedInteger('appointment_id')->unique();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->unsignedInteger('insurance_id')->nullable();
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->unsignedInteger('membership_id')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('status', 30)->default('Chờ xử lý');
            $table->string('payment_method', 50)->nullable();
            $table->dateTime('payment_date')->nullable();
            $table->string('transaction_ref', 100)->nullable();
            $table->timestamps();

            $table->foreign('appointment_id')->references('appointment_id')->on('appointments')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
