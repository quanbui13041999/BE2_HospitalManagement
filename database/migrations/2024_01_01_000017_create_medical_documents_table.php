<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('MedicalDocuments', function (Blueprint $table) {
            $table->increments('doc_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('record_id')->nullable();
            $table->string('doc_type', 50)->nullable();
            $table->string('doc_name', 200)->nullable();
            $table->string('file_path', 500)->nullable();
            $table->dateTime('uploaded_at')->useCurrent();

            $table->foreign('user_id')
                  ->references('user_id')->on('Users')
                  ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('record_id')
                  ->references('record_id')->on('MedicalRecords')
                  ->onUpdate('cascade')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('MedicalDocuments');
    }
};
