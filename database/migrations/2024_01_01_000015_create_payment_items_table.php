<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('PaymentItems', function (Blueprint $table) {
            $table->increments('item_id');
            $table->unsignedInteger('payment_id');
            $table->string('item_type', 30);
            $table->string('item_name', 150)->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->decimal('total_price', 10, 2)->nullable();

            $table->foreign('payment_id')
                  ->references('payment_id')->on('Payments')
                  ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('PaymentItems');
    }
};
