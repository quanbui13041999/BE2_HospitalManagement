<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FoodSeeder extends Seeder
{
    public function run(): void
    {
        $foods = [
            1 => ['Cơm trắng', 130, 'Món ăn phổ biến được nấu từ gạo trắng.'],
            2 => ['Ức gà', 165, 'Thịt gà ít mỡ, giàu protein.'],
            3 => ['Cá hồi', 208, 'Loại cá giàu omega-3 và dinh dưỡng.'],
            4 => ['Trứng gà', 155, 'Nguồn protein và chất béo tốt cho cơ thể.'],
            5 => ['Dưa muối / thực phẩm đóng hộp', 35, 'Thực phẩm nhiều muối, nên hạn chế ở người tăng huyết áp.'],
            6 => ['Cam / ổi', 47, 'Trái cây giàu vitamin C, hỗ trợ miễn dịch và chuyển hóa.'],
            7 => ['Thịt bò / nội tạng động vật', 250, 'Thực phẩm giàu purin và chất béo bão hòa, cần hạn chế ở một số bệnh lý.'],
            8 => ['Bí đỏ / cháo loãng', 55, 'Món mềm, dễ tiêu, phù hợp khi đau hoặc viêm dạ dày.'],
            9 => ['Đồ ăn cay nóng', 120, 'Dễ kích ứng niêm mạc họng và dạ dày.'],
            10 => ['Nước hầm xương', 60, 'Bổ sung khoáng chất và collagen tự nhiên cho xương khớp.'],
            11 => ['Đồ ngọt / bánh kẹo nhiều đường', 420, 'Nhiều đường tinh luyện, không phù hợp với tiểu đường và viêm khớp.'],
            12 => ['Súp gà / cháo hành tía tô', 75, 'Món ấm, dễ tiêu, phù hợp khi cảm cúm hoặc viêm hô hấp.'],
            13 => ['Nước đá / đồ uống lạnh', 0, 'Đồ uống lạnh có thể làm nặng triệu chứng viêm họng hoặc ho.'],
            14 => ['Bông cải xanh / bắp cải', 34, 'Rau họ cải giàu chất chống oxy hóa và chất xơ.'],
            15 => ['Sữa nguyên kem béo', 61, 'Sản phẩm sữa béo, có thể không phù hợp với một số bệnh hô hấp.'],
            16 => ['Mật ong ấm', 304, 'Có thể làm dịu họng và hỗ trợ giảm ho khan.'],
            17 => ['Đồ ăn chiên rán cứng', 312, 'Dễ gây kích ứng họng, tăng viêm và khó tiêu.'],
            18 => ['Rau xanh luộc', 25, 'Nguồn chất xơ, vitamin và khoáng chất, phù hợp nhiều chế độ ăn bệnh lý.'],
            19 => ['Yến mạch', 389, 'Ngũ cốc nguyên hạt giàu beta-glucan, hỗ trợ kiểm soát đường huyết.'],
            20 => ['Khoai lang', 86, 'Tinh bột phức, nhiều chất xơ, thay thế tốt hơn cho cơm trắng ở một số trường hợp.'],
            21 => ['Nước ấm', 0, 'Hỗ trợ làm dịu họng và duy trì đủ nước khi sốt hoặc viêm hô hấp.'],
            22 => ['Sữa chua không đường', 59, 'Dễ tiêu, hỗ trợ hệ vi sinh đường ruột.'],
            23 => ['Cá thu', 205, 'Cá béo giàu omega-3, tốt cho tim mạch và phản ứng viêm.'],
            24 => ['Hạt hạnh nhân', 579, 'Giàu chất béo không bão hòa, vitamin E và magie.'],
            25 => ['Nghệ / gừng', 80, 'Gia vị hỗ trợ tiêu hóa, có đặc tính chống viêm nhẹ.'],
        ];

        foreach ($foods as $foodId => [$name, $calories, $description]) {
            DB::table('foods')->updateOrInsert(
                ['food_id' => $foodId],
                [
                    'food_name' => $name,
                    'calories_per_100g' => $calories,
                    'description' => $description,
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
