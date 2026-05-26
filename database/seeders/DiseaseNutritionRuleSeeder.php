<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DiseaseNutritionRuleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('disease_nutrition_rules')->insertOrIgnore([
            [
                'disease_name' => 'Tiểu đường',
                'icd_code' => 'E11',
                'food_id' => 2, // Ức gà
                'recommendation_type' => 'should_eat',
                'reason' => 'Ức gà giàu protein và ít chất béo.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'disease_name' => 'Tiểu đường',
                'icd_code' => 'E11',
                'food_id' => 1, // Cơm trắng
                'recommendation_type' => 'should_avoid',
                'reason' => 'Cơm trắng có chỉ số đường huyết cao.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'disease_name' => 'Cao huyết áp',
                'icd_code' => 'I10',
                'food_id' => 3, // Cá hồi
                'recommendation_type' => 'should_eat',
                'reason' => 'Cá hồi chứa omega-3 tốt cho tim mạch.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'disease_name' => 'Tim mạch',
                'icd_code' => 'I25',
                'food_id' => 4, // Trứng gà
                'recommendation_type' => 'should_avoid',
                'reason' => 'Ăn quá nhiều lòng đỏ trứng có thể tăng cholesterol.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}