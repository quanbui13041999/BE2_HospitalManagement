<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hospitalnews', function (Blueprint $table) {
            $table->increments('news_id');
            $table->string('title', 200);
            $table->text('content');
            $table->string('category', 50)->nullable();
            $table->string('thumbnail', 500)->nullable();
            $table->unsignedInteger('author_id')->nullable();
            $table->boolean('is_published')->default(true);
            $table->boolean('email_sent')->default(false);
            $table->dateTime('published_at')->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->foreign('author_id')->references('user_id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospitalnews');
    }
};
