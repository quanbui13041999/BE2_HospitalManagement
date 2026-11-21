<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('Medicines', function (Blueprint $table) {
            $table->increments('medicine_id');
            $table->string('medicine_code', 30)->unique()->nullable();
            $table->string('medicine_name', 150);
            $table->string('unit', 30)->nullable();
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->integer('min_stock')->default(10);
            $table->date('expiry_date')->nullable();
            $table->boolean('status')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Medicines');
    }
};
