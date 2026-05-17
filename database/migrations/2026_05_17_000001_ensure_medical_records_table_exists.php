<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if ($this->hasExactTable('medical_records')) {
            return;
        }

        Schema::create('medical_records', function (Blueprint $table) {
            $table->id('record_id');
            $table->string('record_code', 30)->unique()->comment('Ma phieu VD: CK-2026-0001');
            $table->unsignedInteger('patient_id')->nullable();
            $table->string('patient_name', 100);
            $table->string('patient_code', 30)->nullable()->comment('Ma benh nhan');
            $table->unsignedInteger('doctor_id')->nullable();
            $table->string('doctor_name', 100);
            $table->unsignedInteger('appointment_id')->nullable()->comment('FK appointments.appointment_id');
            $table->date('exam_date');
            $table->time('exam_time')->nullable();
            $table->enum('visit_type', ['Tai kham', 'Kham moi', 'Cap cuu', 'Tái khám', 'Khám mới', 'Cấp cứu'])
                ->default('Kham moi');
            $table->string('status', 30)->default('completed');
            $table->text('status_note')->nullable();
            $table->string('chief_complaint', 1000)->nullable()->comment('Ly do den kham');
            $table->timestamps();

            $table->index('patient_id');
            $table->index('doctor_id');
            $table->index('appointment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }

    private function hasExactTable(string $tableName): bool
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return Schema::hasTable($tableName);
        }

        return DB::table('information_schema.TABLES')
            ->where('TABLE_SCHEMA', DB::raw('DATABASE()'))
            ->where('TABLE_NAME', $tableName)
            ->exists();
    }
};
