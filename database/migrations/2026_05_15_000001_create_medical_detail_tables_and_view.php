<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('diagnoses')) {
            Schema::create('diagnoses', function (Blueprint $table) {
                $table->unsignedBigInteger('diagnosis_id', true);
                $table->unsignedBigInteger('record_id')->index();
                $table->string('diagnosis_name', 300);
                $table->string('icd_code', 20)->nullable();
                $table->enum('diagnosis_type', ['primary', 'secondary', 'complication'])->default('primary');
                $table->text('note')->nullable();
                $table->timestamp('created_at')->nullable()->useCurrent();
                $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            });
        }

        if (! Schema::hasTable('medical_attachments')) {
            Schema::create('medical_attachments', function (Blueprint $table) {
                $table->unsignedBigInteger('attachment_id', true);
                $table->unsignedBigInteger('record_id')->index();
                $table->string('file_name');
                $table->string('file_path', 500);
                $table->string('file_type', 50)->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->enum('attachment_category', ['result', 'image', 'document', 'other'])->default('document');
                $table->timestamp('created_at')->nullable()->useCurrent();
                $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            });
        }

        if (! Schema::hasTable('medical_orders')) {
            Schema::create('medical_orders', function (Blueprint $table) {
                $table->unsignedBigInteger('order_id', true);
                $table->unsignedBigInteger('record_id')->index();
                $table->enum('order_type', ['lab', 'imaging', 'other'])->default('lab');
                $table->string('order_name', 300);
                $table->text('description')->nullable();
                $table->string('result_status', 50)->default('Chờ kết quả');
                $table->text('result_note')->nullable();
                $table->timestamp('created_at')->nullable()->useCurrent();
                $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            });
        }

        if (! Schema::hasTable('prescriptions')) {
            Schema::create('prescriptions', function (Blueprint $table) {
                $table->unsignedBigInteger('prescription_id', true);
                $table->unsignedBigInteger('record_id')->index();
                $table->string('drug_name', 200);
                $table->string('dosage', 100)->nullable();
                $table->string('instructions', 500)->nullable();
                $table->unsignedInteger('duration_days')->default(30);
                $table->unsignedInteger('quantity')->nullable();
                $table->string('unit', 30)->nullable();
                $table->timestamp('created_at')->nullable()->useCurrent();
                $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            });
        }

        if (! Schema::hasTable('record_allergies')) {
            Schema::create('record_allergies', function (Blueprint $table) {
                $table->unsignedBigInteger('id', true);
                $table->unsignedBigInteger('record_id')->index();
                $table->string('allergen', 200);
                $table->string('severity', 50)->nullable();
                $table->text('reaction')->nullable();
                $table->timestamp('created_at')->nullable()->useCurrent();
                $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            });
        }

        if (! Schema::hasTable('vital_signs')) {
            Schema::create('vital_signs', function (Blueprint $table) {
                $table->unsignedBigInteger('vital_id', true);
                $table->unsignedBigInteger('record_id')->unique();
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
                $table->timestamp('created_at')->nullable()->useCurrent();
                $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            });
        }

        DB::table('users')->insertOrIgnore([
            'user_id' => 22,
            'full_name' => 'Anh Tú Huỳnh',
            'email' => 'a123@gmail.com',
            'password' => '$2y$12$k8UuiGqtbaAtoyW0UhZQae.yZhnm43aPPvosi0GHy9WUIPDVKE152',
            'phone' => '1234567890',
            'address' => 'a123@gmail.com',
            'date_of_birth' => '2026-05-05',
            'gender' => 'Nam',
            'role_id' => 1,
            'avatar_url' => null,
            'status' => 1,
            'created_at' => '2026-05-07 23:53:09',
        ]);

        DB::table('medical_records')->insertOrIgnore([
            'record_id' => 1,
            'record_code' => 'CK-2026-0001',
            'patient_id' => 22,
            'patient_name' => 'Anh Tú Huỳnh',
            'patient_code' => 'AN2026056285',
            'doctor_id' => 11,
            'doctor_name' => 'Nguyễn Thị Thu',
            'appointment_id' => 32,
            'exam_date' => '2026-05-15',
            'exam_time' => '11:30:00',
            'visit_type' => 'Kham moi',
            'status' => 'completed',
            'status_note' => null,
            'chief_complaint' => 'gfgfgf',
            'created_at' => '2026-05-14 03:18:03',
            'updated_at' => '2026-05-14 03:18:03',
        ]);

        DB::table('diagnoses')->insertOrIgnore([
            'diagnosis_id' => 1,
            'record_id' => 1,
            'diagnosis_name' => 'Viêm khớp',
            'icd_code' => '44',
            'diagnosis_type' => 'secondary',
            'note' => null,
            'created_at' => '2026-05-14 03:18:03',
            'updated_at' => '2026-05-14 03:18:03',
        ]);

        DB::table('vital_signs')->insertOrIgnore([
            'vital_id' => 1,
            'record_id' => 1,
            'blood_pressure' => '124',
            'bp_status' => 'normal',
            'heart_rate' => 75.0,
            'hr_status' => 'normal',
            'temperature' => 34.0,
            'temp_status' => 'normal',
            'spo2' => 66.0,
            'spo2_status' => 'normal',
            'weight' => 66.0,
            'blood_sugar' => 66.00,
            'sugar_status' => 'normal',
            'created_at' => '2026-05-14 03:18:03',
            'updated_at' => '2026-05-14 03:18:03',
        ]);

        DB::statement('DROP VIEW IF EXISTS v_doctorratings');
        DB::statement("
            CREATE VIEW v_doctorratings AS
            SELECT
                d.doctor_id,
                d.full_name,
                d.department_id,
                d.experience,
                d.price,
                d.avatar_url,
                d.bio,
                d.status,
                COALESCE(AVG(r.rating), 0) AS avg_rating,
                COUNT(r.review_id) AS total_reviews
            FROM doctors d
            LEFT JOIN reviews r ON r.doctor_id = d.doctor_id
            GROUP BY
                d.doctor_id,
                d.full_name,
                d.department_id,
                d.experience,
                d.price,
                d.avatar_url,
                d.bio,
                d.status
        ");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_doctorratings');
        Schema::dropIfExists('vital_signs');
        Schema::dropIfExists('record_allergies');
        Schema::dropIfExists('prescriptions');
        Schema::dropIfExists('medical_orders');
        Schema::dropIfExists('medical_attachments');
        Schema::dropIfExists('diagnoses');
    }
};
