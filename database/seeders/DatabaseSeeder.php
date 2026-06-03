<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            $this->command?->warn('Hospital SQL dump seeding is only supported on MySQL.');
            return;
        }

        $dumpPath = base_path('HospitalBookingDB_v.sql');

        if (! is_file($dumpPath)) {
            throw new RuntimeException("SQL dump not found: {$dumpPath}");
        }

        $sql = file_get_contents($dumpPath);

        if ($sql === false) {
            throw new RuntimeException("Unable to read SQL dump: {$dumpPath}");
        }

        DB::statement('DROP VIEW IF EXISTS v_doctorratings');
        DB::statement('DROP TABLE IF EXISTS v_doctorratings');
        DB::connection()->getPdo()->exec('SET FOREIGN_KEY_CHECKS=0');

        try {
            foreach ($this->statements($sql) as $statement) {
                if ($this->shouldSkip($statement)) {
                    continue;
                }

                $statement = $this->makeSafe($statement);

                DB::connection()->getPdo()->exec($statement);
            }
        } finally {
            DB::connection()->getPdo()->exec('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->repairMembershipCardsSchema();
        $this->seedMedicalDetailBaseline();
        $this->createDoctorRatingsView();

        $this->call([
            FoodSeeder::class,
            UserSeeder::class,
            NutritionArticleSeeder::class,
            DiseaseNutritionRuleSeeder::class,
        ]);

    }

    private function shouldSkip(string $statement): bool
    {
        $normalized = strtolower(trim(preg_replace('/\s+/', ' ', $statement)));

        if ($normalized === '') {
            return true;
        }

        return str_starts_with($normalized, 'create database')
            || str_starts_with($normalized, 'use ')
            || str_starts_with($normalized, 'start transaction')
            || str_starts_with($normalized, 'commit')
            || str_starts_with($normalized, 'drop table if exists')
            || str_starts_with($normalized, 'drop view if exists')
            || str_starts_with($normalized, 'create table if not exists `migrations`')
            || str_starts_with($normalized, 'create table if not exists `v_doctorratings`')
            || str_starts_with($normalized, 'insert into `migrations`');
    }

    private function makeSafe(string $statement): string
    {
        $statement = preg_replace('/^insert\s+into\s+/i', 'INSERT IGNORE INTO ', $statement, 1) ?? $statement;
        $statement = preg_replace('/\s+DEFINER=`[^`]+`@`[^`]+`/i', '', $statement) ?? $statement;

        return $statement;
    }

    /**
     * @return array<int, string>
     */
    private function statements(string $sql): array
    {
        $statements = [];
        $current = '';
        $quote = null;
        $length = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $next = $i + 1 < $length ? $sql[$i + 1] : '';

            if ($quote === null && $char === '-' && $next === '-') {
                while ($i < $length && $sql[$i] !== "\n") {
                    $i++;
                }
                continue;
            }

            if ($quote === null && $char === '/' && $next === '*') {
                $end = strpos($sql, '*/', $i + 2);
                if ($end === false) {
                    break;
                }
                $i = $end + 1;
                continue;
            }

            if (($char === "'" || $char === '"') && ($i === 0 || $sql[$i - 1] !== '\\')) {
                $quote = $quote === $char ? null : ($quote ?? $char);
            }

            if ($char === ';' && $quote === null) {
                $statement = trim($current);
                if ($statement !== '') {
                    $statements[] = $statement;
                }
                $current = '';
                continue;
            }

            $current .= $char;
        }

        $statement = trim($current);
        if ($statement !== '') {
            $statements[] = $statement;
        }

        return $statements;
    }

    private function seedMedicalDetailBaseline(): void
    {
        if (
            ! DB::getSchemaBuilder()->hasTable('medical_records')
            || ! DB::getSchemaBuilder()->hasTable('diagnoses')
            || ! DB::getSchemaBuilder()->hasTable('vital_signs')
        ) {
            return;
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
    }

    private function repairMembershipCardsSchema(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('membershipcards')) {
            return;
        }

        if (! DB::getSchemaBuilder()->hasColumn('membershipcards', 'total_spent')) {
            DB::statement('ALTER TABLE membershipcards ADD total_spent DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER points');
        }
    }

    private function createDoctorRatingsView(): void
    {
        if (
            ! DB::getSchemaBuilder()->hasTable('doctors')
            || ! DB::getSchemaBuilder()->hasTable('reviews')
        ) {
            return;
        }

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
}
