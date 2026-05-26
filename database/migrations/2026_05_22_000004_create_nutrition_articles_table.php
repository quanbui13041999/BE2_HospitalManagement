<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nutrition_articles', function (Blueprint $table) {
            $table->increments('article_id');
            // FK → doctors.doctor_id (nullable vì admin cũng viết được)
            $table->unsignedInteger('doctor_id')->nullable();
            $table->foreign('doctor_id')->references('doctor_id')->on('doctors')->onDelete('set null');
            $table->string('title', 255);
           $table->string('slug', 191)->unique();
            $table->longText('content');
            $table->string('target_disease', 200)->nullable()->comment('Tên bệnh để lọc bài cho bệnh nhân');
            $table->tinyInteger('status')->default(0)->comment('0=Nháp, 1=Xuất bản');
            $table->timestamps();
            $table->index(['target_disease', 'status'], 'idx_disease_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nutrition_articles');
    }
};
