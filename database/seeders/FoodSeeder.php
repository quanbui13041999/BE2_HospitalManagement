<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FoodSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('foods')->insertOrIgnore([
            [
                'food_name' => 'Cơm trắng',
                'calories_per_100g' => 130,
                'description' => 'Món ăn phổ biến được nấu từ gạo trắng.',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'food_name' => 'Ức gà',
                'calories_per_100g' => 165,
                'description' => 'Thịt gà ít mỡ, giàu protein.',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'food_name' => 'Cá hồi',
                'calories_per_100g' => 208,
                'description' => 'Loại cá giàu omega-3 và dinh dưỡng.',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'food_name' => 'Trứng gà',
                'calories_per_100g' => 155,
                'description' => 'Nguồn protein và chất béo tốt cho cơ thể.',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}