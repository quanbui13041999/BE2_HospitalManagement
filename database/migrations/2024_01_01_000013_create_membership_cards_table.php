<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('MembershipCards', function (Blueprint $table) {
            $table->increments('card_id');
            $table->unsignedInteger('user_id')->unique();
            $table->string('card_number', 50)->unique();
            $table->string('tier', 30)->default('Thường');
            $table->integer('points')->default(0);
            $table->decimal('discount_pct', 5, 2)->default(0);
            $table->date('issue_date')->useCurrent();
            $table->date('expiry_date')->nullable();
            $table->boolean('status')->default(true);

            $table->foreign('user_id')
                  ->references('user_id')->on('Users')
                  ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('MembershipCards');
    }
};
