<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('Invoices', function (Blueprint $table) {
            $table->increments('invoice_id');
            $table->string('invoice_number', 50)->unique();
            $table->unsignedInteger('patient_id');
            $table->unsignedInteger('doctor_id')->nullable();
            $table->unsignedInteger('appointment_id')->nullable();
            $table->unsignedInteger('bhyt_card_id')->nullable();
            $table->dateTime('issue_date')->useCurrent();
            $table->dateTime('due_date')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->boolean('bhyt_applied')->default(false);
            $table->unsignedInteger('bhyt_coverage')->default(0);
            $table->decimal('bhyt_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('status', 30)->default('Chờ thanh toán');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('patient_id')
                  ->references('user_id')->on('Users')
                  ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('doctor_id')
                  ->references('user_id')->on('Users')
                  ->onUpdate('cascade')->onDelete('set null');

            $table->foreign('appointment_id')
                  ->references('appointment_id')->on('Appointments')
                  ->onUpdate('cascade')->onDelete('set null');

            $table->foreign('bhyt_card_id')
                  ->references('bhyt_card_id')->on('bhyt_cards')
                  ->onUpdate('cascade')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Invoices');
    }
};
