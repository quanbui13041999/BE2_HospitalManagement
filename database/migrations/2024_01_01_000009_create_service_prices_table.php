<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ServicePrices', function (Blueprint $table) {
            $table->increments('price_id');
            $table->unsignedInteger('service_id');
            $table->string('price_type', 30);
            $table->decimal('price', 10, 2);
            $table->date('effective_date')->useCurrent();
            $table->date('end_date')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->foreign('service_id')
                  ->references('service_id')->on('Services')
                  ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('created_by')
                  ->references('user_id')->on('Users')
                  ->onUpdate('cascade')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ServicePrices');
    }
};
