<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->increments('payment_id');
            $table->unsignedInteger('invoice_id');
            $table->string('payment_method', 50)->default('QR');
            $table->decimal('amount', 12, 2);
            $table->string('status', 30)->default('Chờ xử lý');
            $table->dateTime('paid_at')->nullable();
            $table->string('transaction_ref', 100)->nullable();

            $table->foreign('invoice_id')
                  ->references('invoice_id')->on('Invoices')
                  ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Payments');
    }
};
