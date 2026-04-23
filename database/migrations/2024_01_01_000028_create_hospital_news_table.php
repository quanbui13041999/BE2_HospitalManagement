<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('HospitalNews', function (Blueprint $table) {
            $table->increments('news_id');
            $table->string('title', 300);
            $table->longText('content')->nullable();
            $table->string('category', 50)->nullable();
            $table->string('thumbnail', 500)->nullable();
            $table->unsignedInteger('author_id')->nullable();
            $table->boolean('is_published')->default(false);
            $table->dateTime('published_at')->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->foreign('author_id')
                  ->references('user_id')->on('Users')
                  ->onUpdate('cascade')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('HospitalNews');
    }
};
