<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('insurancecards', function (Blueprint $table) {
            $table->increments('insurance_id');
            $table->unsignedInteger('user_id');
            $table->string('card_number', 50);
            $table->string('provider', 100)->nullable();
            $table->date('issued_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('discount_pct', 5, 2)->default(0);
            $table->string('status', 20)->default('Còn hạn');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->nullable();

            $table->foreign('user_id')
                  ->references('user_id')->on('users')
                  ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurancecards');
    }
};
