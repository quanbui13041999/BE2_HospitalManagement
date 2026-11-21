<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('health_trackings')) {
            Schema::create('health_trackings', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('patient_id');
                $table->integer('systolic');
                $table->integer('diastolic');
                $table->integer('heart_rate');
                $table->integer('spo2');
                $table->decimal('weight', 5, 2);
                $table->integer('blood_sugar');
                $table->text('symptoms')->nullable();
                $table->enum('risk_level', ['normal', 'warning', 'danger'])->default('normal');
                $table->json('risk_warnings')->nullable();
                $table->unsignedBigInteger('version')->default(1);
                $table->timestamps();
                $table->softDeletes();

                $table->index('patient_id');
            });
        }

        DB::table('health_trackings')->insertOrIgnore([
            [
                'id' => 1,
                'patient_id' => 25,
                'systolic' => 110,
                'diastolic' => 130,
                'heart_rate' => 110,
                'spo2' => 90,
                'weight' => 58.00,
                'blood_sugar' => 80,
                'symptoms' => 'Mệt mỏi',
                'risk_level' => 'danger',
                'risk_warnings' => json_encode([
                    [
                        'icon' => 'bi-heart-pulse-fill',
                        'field' => 'diastolic',
                        'level' => 'danger',
                        'message' => 'Huyết áp tâm trương rất cao (130 mmHg) - Nguy hiểm!',
                    ],
                    [
                        'icon' => 'bi-lungs',
                        'field' => 'spo2',
                        'level' => 'warning',
                        'message' => 'Nồng độ oxy thấp (90%) - Cần theo dõi.',
                    ],
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'version' => 3,
                'created_at' => '2026-05-27 08:58:30',
                'updated_at' => '2026-05-27 09:00:46',
                'deleted_at' => null,
            ],
        ]);
    }

    public function down(): void
    {
        // Keep patient tracking data intact.
    }
};
