<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NutritionSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // =============================================
        // 1. FOODS
        // =============================================
        DB::table('foods')->insert([
            ['food_name' => 'Cơm trắng',       'calories_per_100g' => 130, 'description' => 'Gạo tẻ nấu chín, tinh bột cao',                          'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['food_name' => 'Ức gà luộc',      'calories_per_100g' => 165, 'description' => 'Protein cao, ít chất béo',                                'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['food_name' => 'Bánh ngọt',       'calories_per_100g' => 400, 'description' => 'Nhiều đường và chất béo bão hòa',                         'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['food_name' => 'Rau muống luộc',  'calories_per_100g' =>  19, 'description' => 'Rau xanh nhiều chất xơ, vitamin',                        'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['food_name' => 'Cá hồi hấp',     'calories_per_100g' => 206, 'description' => 'Giàu Omega-3, tốt cho tim mạch',                          'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['food_name' => 'Nước ngọt có ga', 'calories_per_100g' =>  41, 'description' => 'Đường fructose cao, không có giá trị dinh dưỡng',         'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['food_name' => 'Yến mạch',        'calories_per_100g' => 389, 'description' => 'Beta-glucan kiểm soát đường huyết',                      'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['food_name' => 'Trứng gà luộc',   'calories_per_100g' => 155, 'description' => 'Protein hoàn chỉnh, dễ tiêu hóa',                        'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['food_name' => 'Khoai lang',      'calories_per_100g' =>  86, 'description' => 'Tinh bột phức, chỉ số GI thấp hơn cơm',                   'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['food_name' => 'Thịt bò',         'calories_per_100g' => 250, 'description' => 'Protein cao nhưng nhiều chất béo bão hòa',                'status' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);

        $foodMap = DB::table('foods')->pluck('food_id', 'food_name');

        // =============================================
        // 2. DISEASE_NUTRITION_RULES
        // =============================================
        $rules = [
            // Tiểu đường
            ['disease_name' => 'Đái tháo đường', 'icd_code' => 'E11', 'food' => 'Bánh ngọt',      'type' => 'should_avoid', 'reason' => 'Đường đơn gây tăng đường huyết đột ngột'],
            ['disease_name' => 'Đái tháo đường', 'icd_code' => 'E11', 'food' => 'Nước ngọt có ga','type' => 'should_avoid', 'reason' => 'Fructose làm kháng insulin, tăng triglyceride'],
            ['disease_name' => 'Đái tháo đường', 'icd_code' => 'E11', 'food' => 'Cơm trắng',      'type' => 'should_avoid', 'reason' => 'GI cao, nên thay bằng gạo lứt hoặc khoai lang'],
            ['disease_name' => 'Đái tháo đường', 'icd_code' => 'E11', 'food' => 'Ức gà luộc',     'type' => 'should_eat',   'reason' => 'Protein nạc không ảnh hưởng đường huyết'],
            ['disease_name' => 'Đái tháo đường', 'icd_code' => 'E11', 'food' => 'Yến mạch',       'type' => 'should_eat',   'reason' => 'Beta-glucan giúp kiểm soát đường huyết sau ăn'],
            ['disease_name' => 'Đái tháo đường', 'icd_code' => 'E11', 'food' => 'Rau muống luộc', 'type' => 'should_eat',   'reason' => 'Chất xơ cao, ít calo, nhiều vitamin K'],
            ['disease_name' => 'Đái tháo đường', 'icd_code' => 'E11', 'food' => 'Khoai lang',     'type' => 'should_eat',   'reason' => 'GI thấp hơn cơm trắng, nhiều chất xơ'],
            // Tim mạch
            ['disease_name' => 'Bệnh tim mạch',  'icd_code' => 'I25', 'food' => 'Thịt bò',        'type' => 'should_avoid', 'reason' => 'Chất béo bão hòa làm tăng LDL-cholesterol'],
            ['disease_name' => 'Bệnh tim mạch',  'icd_code' => 'I25', 'food' => 'Bánh ngọt',      'type' => 'should_avoid', 'reason' => 'Chất béo trans tăng nguy cơ xơ vữa động mạch'],
            ['disease_name' => 'Bệnh tim mạch',  'icd_code' => 'I25', 'food' => 'Cá hồi hấp',     'type' => 'should_eat',   'reason' => 'Omega-3 giúp giảm triglyceride, chống viêm'],
            ['disease_name' => 'Bệnh tim mạch',  'icd_code' => 'I25', 'food' => 'Rau muống luộc', 'type' => 'should_eat',   'reason' => 'Kali và magie hỗ trợ huyết áp ổn định'],
            // Tăng huyết áp
            ['disease_name' => 'Tăng huyết áp',  'icd_code' => 'I10', 'food' => 'Nước ngọt có ga','type' => 'should_avoid', 'reason' => 'Sodium ẩn và đường cao làm tăng huyết áp'],
            ['disease_name' => 'Tăng huyết áp',  'icd_code' => 'I10', 'food' => 'Thịt bò',        'type' => 'should_avoid', 'reason' => 'Chất béo bão hòa gây xơ vữa, tăng áp lực mạch'],
            ['disease_name' => 'Tăng huyết áp',  'icd_code' => 'I10', 'food' => 'Rau muống luộc', 'type' => 'should_eat',   'reason' => 'Kali trong rau giúp hạ huyết áp tự nhiên'],
            ['disease_name' => 'Tăng huyết áp',  'icd_code' => 'I10', 'food' => 'Cá hồi hấp',     'type' => 'should_eat',   'reason' => 'Omega-3 giúp giãn mạch, giảm huyết áp'],
        ];

        foreach ($rules as $r) {
            $foodId = $foodMap[$r['food']] ?? null;
            if (!$foodId) continue;
            DB::table('disease_nutrition_rules')->updateOrInsert(
                ['disease_name' => $r['disease_name'], 'food_id' => $foodId],
                ['icd_code' => $r['icd_code'], 'recommendation_type' => $r['type'], 'reason' => $r['reason'], 'created_at' => $now, 'updated_at' => $now]
            );
        }

        // =============================================
        // 3. NUTRITION_ARTICLES
        // =============================================
        $articles = [
            [
                'doctor_id'      => 1,
                'title'          => 'Chế độ ăn uống cho người bệnh Tiểu đường Type 2',
                'slug'           => 'che-do-an-uong-tieu-duong-type-2',
                'content'        => '<h2>Nguyên tắc cơ bản</h2><p>Người bệnh tiểu đường type 2 cần kiểm soát lượng carbohydrate, ưu tiên thực phẩm có chỉ số đường huyết (GI) thấp như yến mạch, rau xanh và các loại đậu.</p><h2>Thực phẩm nên ăn</h2><ul><li>Rau xanh các loại</li><li>Cá, thịt gà không da</li><li>Ngũ cốc nguyên hạt, yến mạch</li><li>Khoai lang thay cơm trắng</li></ul><h2>Thực phẩm nên tránh</h2><ul><li>Đường, bánh kẹo ngọt</li><li>Nước ngọt có ga</li><li>Cơm trắng số lượng lớn</li></ul>',
                'target_disease' => 'Đái tháo đường',
                'status'         => 1,
            ],
            [
                'doctor_id'      => 2,
                'title'          => 'Dinh dưỡng bảo vệ tim mạch - Những điều cần biết',
                'slug'           => 'dinh-duong-bao-ve-tim-mach',
                'content'        => '<h2>Tại sao dinh dưỡng quan trọng?</h2><p>Chế độ ăn nhiều chất béo bão hòa làm tăng LDL-cholesterol, xơ vữa động mạch. Điều chỉnh dinh dưỡng giảm đến 30% nguy cơ biến cố tim mạch.</p><h2>Khuyến nghị hàng ngày</h2><p>Ưu tiên cá béo (cá hồi, cá thu), rau củ đa màu sắc. Hạn chế thịt đỏ và thực phẩm chế biến sẵn.</p>',
                'target_disease' => 'Bệnh tim mạch',
                'status'         => 1,
            ],
            [
                'doctor_id'      => null,
                'title'          => 'Kiểm soát huyết áp bằng chế độ ăn DASH',
                'slug'           => 'kiem-soat-huyet-ap-bang-che-do-an-dash',
                'content'        => '<h2>Chế độ ăn DASH là gì?</h2><p>DASH (Dietary Approaches to Stop Hypertension) được nghiên cứu lâm sàng chứng minh giảm huyết áp tâm thu 8-14 mmHg sau 2 tuần.</p><p>Nguyên tắc: nhiều rau, trái cây, ngũ cốc nguyên hạt; ít muối (dưới 2300mg natri/ngày), ít chất béo bão hòa.</p>',
                'target_disease' => 'Tăng huyết áp',
                'status'         => 1,
            ],
        ];

        foreach ($articles as $a) {
            DB::table('nutrition_articles')->updateOrInsert(
                ['slug' => $a['slug']],
                array_merge($a, ['created_at' => $now, 'updated_at' => $now])
            );
        }

        $this->command->info('✅ NutritionSeeder hoàn thành!');
    }
}
