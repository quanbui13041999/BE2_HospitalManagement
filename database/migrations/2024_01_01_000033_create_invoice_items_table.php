<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->increments('item_id');
            $table->unsignedInteger('invoice_id');
            $table->unsignedInteger('service_id')->nullable();
            $table->string('service_name', 255);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('subtotal', 12, 2);

            $table->foreign('invoice_id')
                  ->references('invoice_id')->on('Invoices')
                  ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('service_id')
                  ->references('service_id')->on('Services')
                  ->onUpdate('cascade')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
