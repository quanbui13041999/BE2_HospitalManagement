<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DiseaseNutritionRuleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('disease_nutrition_rules')->insertOrIgnore([
            // 1. Đái tháo đường type 2 (ICD-10: E11)
            [
                'disease_name' => 'Đái tháo đường type 2',
                'icd_code' => 'E11',
                'food_id' => 2, // Ức gà
                'recommendation_type' => 'should_eat',
                'reason' => 'Ức gà cung cấp protein nạc chất lượng cao, giúp duy trì khối cơ mà không làm tăng đột ngột chỉ số đường huyết sau ăn.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'disease_name' => 'Đái tháo đường type 2',
                'icd_code' => 'E11',
                'food_id' => 1, // Cơm trắng
                'recommendation_type' => 'should_avoid',
                'reason' => 'Cơm trắng có chỉ số đường huyết (GI) cao, dễ chuyển hóa thành glucose nhanh gây áp lực lớn lên insulin.',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 2. Tăng huyết áp (ICD-10: I10)
            [
                'disease_name' => 'Tăng huyết áp',
                'icd_code' => 'I10',
                'food_id' => 3, // Cá hồi
                'recommendation_type' => 'should_eat',
                'reason' => 'Cá hồi rất giàu axit béo Omega-3 giúp hạ huyết áp, giảm viêm và hạn chế nguy cơ hình thành cục máu đông.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'disease_name' => 'Tăng huyết áp',
                'icd_code' => 'I10',
                'food_id' => 5, // Dưa muối / Thực phẩm đóng hộp
                'recommendation_type' => 'should_avoid',
                'reason' => 'Chứa hàm lượng natri (muối) cực cao, gây giữ nước trong lòng mạch và làm tăng áp lực lên thành mạch máu.',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 3. Gout (ICD-10: M10)
            [
                'disease_name' => 'Gout',
                'icd_code' => 'M10',
                'food_id' => 6, // Trái cây giàu Vitamin C (Cam, Ổi)
                'recommendation_type' => 'should_eat',
                'reason' => 'Vitamin C giúp hỗ trợ thận tăng cường đào thải axit uric ra khỏi cơ thể qua đường tiết niệu.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'disease_name' => 'Gout',
                'icd_code' => 'M10',
                'food_id' => 7, // Thịt bò / Nội tạng động vật
                'recommendation_type' => 'should_avoid',
                'reason' => 'Chứa hàm lượng nhân purin rất cao, khi vào cơ thể sẽ chuyển hóa trực tiếp thành axit uric gây ra các cơn đau khớp cấp.',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 4. Viêm dạ dày (ICD-10: K29)
            [
                'disease_name' => 'Viêm dạ dày',
                'icd_code' => 'K29',
                'food_id' => 8, // Bí đỏ / Cháo loãng
                'recommendation_type' => 'should_eat',
                'reason' => 'Thực phẩm mềm, giàu chất xơ hòa tan giúp bao bọc niêm mạc dạ dày, dễ tiêu hóa và trung hòa bớt lượng axit dư thừa.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'disease_name' => 'Viêm dạ dày',
                'icd_code' => 'K29',
                'food_id' => 9, // Đồ ăn cay nóng / Tiêu / Ớt
                'recommendation_type' => 'should_avoid',
                'reason' => 'Kích ứng trực tiếp các vết loét, làm tăng tiết dịch vị acid gây đau thượng vị, ợ chua và làm tổn thương nặng hơn.',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 5. Thoái hóa khớp (ICD-10: M19)
            [
                'disease_name' => 'Thoái hóa khớp',
                'icd_code' => 'M19',
                'food_id' => 10, // Nước hầm xương
                'recommendation_type' => 'should_eat',
                'reason' => 'Cung cấp glucosamine và chondroitin tự nhiên, giúp bổ sung chất nền sụn khớp và bôi trơn các đầu khớp.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'disease_name' => 'Thoái hóa khớp',
                'icd_code' => 'M19',
                'food_id' => 11, // Đồ ngọt / Bánh kẹo nhiều đường
                'recommendation_type' => 'should_avoid',
                'reason' => 'Đường tinh luyện kích hoạt phản ứng viêm hệ thống Cytokine, làm trầm trọng thêm tình trạng đau và sưng tấy tại ổ khớp.',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 6. Cúm A & Cúm B (ICD-10: J10)
            [
                'disease_name' => 'Cúm A',
                'icd_code' => 'J10',
                'food_id' => 12, // Súp gà / Cháo hành tía tô
                'recommendation_type' => 'should_eat',
                'reason' => 'Món ăn lỏng ấm giúp làm loãng dịch nhầy ở mũi họng, dễ tiêu hóa và cung cấp năng lượng nhanh cho cơ thể đang mệt mỏi.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'disease_name' => 'Cúm B',
                'icd_code' => 'J10',
                'food_id' => 13, // Nước đá / Đồ uống lạnh
                'recommendation_type' => 'should_avoid',
                'reason' => 'Đồ lạnh làm giảm tuần hoàn tại chỗ vùng họng, dễ gây co thắt và tạo điều kiện cho vi khuẩn cơ hội tấn công làm bội nhiễm.',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 7. COVID-19 (ICD-10: U07.1)
            [
                'disease_name' => 'COVID-19',
                'icd_code' => 'U07.1',
                'food_id' => 4, // Trứng gà
                'recommendation_type' => 'should_eat',
                'reason' => 'Là nguồn protein toàn diện có tính sinh khả dụng cao, chứa Vitamin D giúp củng cố hàng rào miễn dịch của cơ thể.',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 8. Viêm phổi & Viêm phế quản (ICD-10: J18 / J40)
            [
                'disease_name' => 'Viêm phổi',
                'icd_code' => 'J18',
                'food_id' => 14, // Rau bắp cải / Bông cải xanh
                'recommendation_type' => 'should_eat',
                'reason' => 'Chứa nhiều chất chống oxy hóa mạnh giúp bảo vệ nhu mô phổi khỏi các tổn thương do gốc tự do trong phản ứng viêm.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'disease_name' => 'Viêm phế quản',
                'icd_code' => 'J40',
                'food_id' => 15, // Sữa nguyên kem béo
                'recommendation_type' => 'should_avoid',
                'reason' => 'Ở một số cơ địa, các sản phẩm sữa béo có thể làm tăng độ đặc của đờm (nhầy) trong phế quản, gây khó khạc.',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 9. Viêm họng & Viêm amidan (ICD-10: J02 / J03)
            [
                'disease_name' => 'Viêm họng',
                'icd_code' => 'J02',
                'food_id' => 16, // Mật ong ấm
                'recommendation_type' => 'should_eat',
                'reason' => 'Mật ong có tính kháng khuẩn tự nhiên, làm dịu niêm mạc họng bị tổn thương và giảm phản xạ ho khan.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'disease_name' => 'Viêm amidan',
                'icd_code' => 'J03',
                'food_id' => 17, // Đồ ăn chiên rán cứng (khoai tây chiên, gà rán)
                'recommendation_type' => 'should_avoid',
                'reason' => 'Cạnh sắc và độ cứng của đồ chiên rán khi nuốt sẽ ma sát mạnh vào khối amidan đang sưng, gây chảy máu hoặc đau nhói.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}