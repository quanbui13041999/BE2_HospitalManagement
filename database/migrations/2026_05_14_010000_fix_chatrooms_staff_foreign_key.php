<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('chatrooms', function (Blueprint $table) {
            $table->dropForeign(['doctor_id']);
            $table->foreign('doctor_id')
                ->references('user_id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('chatrooms', function (Blueprint $table) {
            $table->dropForeign(['doctor_id']);
            $table->foreign('doctor_id')
                ->references('doctor_id')
                ->on('doctors')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }
};
