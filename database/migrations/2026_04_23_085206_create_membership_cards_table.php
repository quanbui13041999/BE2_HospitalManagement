<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('membershipcards') || Schema::hasTable('MembershipCards')) {
            return;
        }

        Schema::create('membershipcards', function (Blueprint $table) {
            $table->increments('card_id');
            $table->unsignedInteger('user_id')->unique();
            $table->string('card_number', 50)->unique();
            $table->string('tier', 30)->default('Đồng');
            $table->integer('points')->default(0);
            $table->decimal('discount_pct', 5, 2)->default(0);
            $table->date('issue_date')->useCurrent();
            $table->date('expiry_date')->nullable();
            $table->boolean('status')->default(true);

            $table->foreign('user_id')
                ->references('user_id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membershipcards');
    }
};
