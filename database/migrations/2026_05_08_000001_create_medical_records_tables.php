<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Bảng hồ sơ bệnh án chi tiết
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id('record_id');
            $table->string('record_code', 30)->unique()->comment('Mã phiếu VD: CK-2026-0412');
            $table->unsignedInteger('patient_id')->nullable();  // ← SỬA: thêm ->nullable()
            $table->string('patient_name', 100);
            $table->string('patient_code', 30)->nullable()->comment('Mã bệnh nhân');
            $table->unsignedInteger('doctor_id')->nullable();    // ← SỬA: thêm ->nullable()
            $table->string('doctor_name', 100);
            $table->unsignedInteger('appointment_id')->nullable();
            $table->date('exam_date');
            $table->time('exam_time')->nullable();
            $table->enum('visit_type', ['Tái khám', 'Khám mới', 'Cấp cứu'])->default('Khám mới');
            $table->string('chief_complaint', 1000)->nullable()->comment('Lý do đến khám');
            $table->timestamps();
        });

        // 2. Bảng chỉ số sinh tồn
        Schema::create('vital_signs', function (Blueprint $table) {
            $table->id('vital_id');
            $table->unsignedBigInteger('record_id');
            $table->string('blood_pressure', 20)->nullable();
            $table->enum('bp_status', ['normal', 'high', 'low'])->default('normal');
            $table->decimal('heart_rate', 5, 1)->nullable();
            $table->enum('hr_status', ['normal', 'high', 'low'])->default('normal');
            $table->decimal('temperature', 4, 1)->nullable();
            $table->enum('temp_status', ['normal', 'high', 'low'])->default('normal');
            $table->decimal('spo2', 5, 1)->nullable();
            $table->enum('spo2_status', ['normal', 'high', 'low'])->default('normal');
            $table->decimal('weight', 5, 1)->nullable();
            $table->decimal('blood_sugar', 5, 2)->nullable();
            $table->enum('sugar_status', ['normal', 'high', 'low'])->default('normal');
            $table->timestamps();

            $table->foreign('record_id')->references('record_id')->on('medical_records')->onDelete('cascade');
        });

        // 3. Bảng chẩn đoán
        Schema::create('diagnoses', function (Blueprint $table) {
            $table->id('diagnosis_id');
            $table->unsignedBigInteger('record_id');
            $table->string('diagnosis_name', 300);
            $table->string('icd_code', 20)->nullable();
            $table->enum('diagnosis_type', ['primary', 'secondary', 'complication'])->default('primary');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('record_id')->references('record_id')->on('medical_records')->onDelete('cascade');
        });

        // 4. Bảng đơn thuốc
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id('prescription_id');
            $table->unsignedBigInteger('record_id');
            $table->string('drug_name', 200);
            $table->string('dosage', 100)->nullable();
            $table->string('instructions', 500)->nullable();
            $table->unsignedInteger('duration_days')->default(30);
            $table->unsignedInteger('quantity')->nullable();
            $table->string('unit', 30)->nullable();
            $table->timestamps();

            $table->foreign('record_id')->references('record_id')->on('medical_records')->onDelete('cascade');
        });

        // 5. Bảng chỉ định xét nghiệm / hình ảnh
        Schema::create('medical_orders', function (Blueprint $table) {
            $table->id('order_id');
            $table->unsignedBigInteger('record_id');
            $table->enum('order_type', ['lab', 'imaging', 'other'])->default('lab');
            $table->string('order_name', 300);
            $table->text('description')->nullable();
            $table->string('result_status', 50)->default('Chờ kết quả');
            $table->text('result_note')->nullable();
            $table->timestamps();

            $table->foreign('record_id')->references('record_id')->on('medical_records')->onDelete('cascade');
        });

        // 6. Bảng tập đính kèm hồ sơ bệnh án
        Schema::create('medical_attachments', function (Blueprint $table) {
            $table->id('attachment_id');
            $table->unsignedBigInteger('record_id');
            $table->string('file_name', 255);
            $table->string('file_path', 500);
            $table->string('file_type', 50)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->enum('attachment_category', ['result', 'image', 'document', 'other'])->default('document');
            $table->timestamps();

            $table->foreign('record_id')->references('record_id')->on('medical_records')->onDelete('cascade');
        });

        // 7. Bảng dị ứng bệnh nhân
        Schema::create('record_allergies', function (Blueprint $table) {
            $table->id();  // ← Cột này sẽ là 'id', không phải 'allergy_id'
            $table->unsignedBigInteger('record_id');
            $table->string('allergen', 200);
            $table->string('severity', 50)->nullable();
            $table->text('reaction')->nullable();
            $table->timestamps();

            $table->foreign('record_id')->references('record_id')->on('medical_records')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('record_allergies');
        Schema::dropIfExists('medical_attachments');
        Schema::dropIfExists('medical_orders');
        Schema::dropIfExists('prescriptions');
        Schema::dropIfExists('diagnoses');
        Schema::dropIfExists('vital_signs');
        Schema::dropIfExists('medical_records');
    }
};