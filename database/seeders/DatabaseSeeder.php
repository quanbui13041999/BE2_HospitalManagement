<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            $this->command?->warn('Baseline data seeding is only supported on MySQL.');
            return;
        }

        $this->repairMembershipCardsSchema();

        $this->call([
            SqlDumpDataSeeder::class,
            DoctorScheduleAvailabilitySeeder::class,
            FoodSeeder::class,
            NutritionArticleSeeder::class,
            DiseaseNutritionRuleSeeder::class,
            DeviceSeeder::class,
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
}
