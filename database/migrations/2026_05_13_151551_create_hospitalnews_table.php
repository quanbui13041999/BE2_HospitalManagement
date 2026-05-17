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
        if (!Schema::hasTable('hospitalnews') && !Schema::hasTable('HospitalNews')) {
            Schema::create('hospitalnews', function (Blueprint $table) {
                $table->increments('news_id');
                $table->string('title');
                $table->text('content');
                $table->string('category')->default('Thông báo');
                $table->string('thumbnail')->nullable();
                $table->unsignedBigInteger('author_id');
                $table->tinyInteger('is_published')->default(0);
                $table->tinyInteger('email_sent')->default(0);
                $table->dateTime('published_at')->nullable();
                $table->dateTime('created_at')->useCurrent();
                
                $table->foreign('author_id')->references('user_id')->on('users')->onDelete('cascade');
            });
        } else {
            $tableName = Schema::hasTable('hospitalnews') ? 'hospitalnews' : 'HospitalNews';

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'email_sent')) {
                    $table->tinyInteger('email_sent')->default(0)->after('is_published');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not dropping if it was pre-existing? 
        // For safety in this environment, let's just keep it.
    }
};
