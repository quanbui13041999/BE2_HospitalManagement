<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('Payments', function (Blueprint $table) {
            $table->increments('payment_id');
            $table->unsignedInteger('appointment_id')->unique();
            $table->unsignedInteger('insurance_id')->nullable();
            $table->unsignedInteger('membership_id')->nullable();
            $table->decimal('subtotal', 10, 2)->nullable();
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->string('method', 50)->nullable();
            $table->string('status', 30)->default('Chưa thanh toán');
            $table->string('transaction_ref', 100)->nullable();
            $table->dateTime('payment_date')->useCurrent();
            $table->string('notes', 255)->nullable();

            $table->foreign('appointment_id')
                  ->references('appointment_id')->on('Appointments')
                  ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('insurance_id')
                  ->references('insurance_id')->on('InsuranceCards')
                  ->onUpdate('cascade')->onDelete('set null');

            $table->foreign('membership_id')
                  ->references('card_id')->on('MembershipCards')
                  ->onUpdate('cascade')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Payments');
    }
};
