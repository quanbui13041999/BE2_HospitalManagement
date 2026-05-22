<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('MedicineTransactions', function (Blueprint $table) {
            $table->increments('transaction_id');
            $table->unsignedInteger('medicine_id');
            $table->string('trans_type', 10);
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->unsignedInteger('reference_id')->nullable();
            $table->string('note', 255)->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->foreign('medicine_id')
                  ->references('medicine_id')->on('Medicines')
                  ->onUpdate('cascade')->onDelete('restrict');

            $table->foreign('created_by')
                  ->references('user_id')->on('Users')
                  ->onUpdate('cascade')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('MedicineTransactions');
    }
};
