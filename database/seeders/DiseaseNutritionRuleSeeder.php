<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DiseaseNutritionRuleSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(FoodSeeder::class);

        $foodIds = DB::table('foods')->pluck('food_id', 'food_name');

        $rules = [
            // Bệnh trong form sửa hồ sơ bệnh án
            ['Đái tháo đường type 2', 'E11', 'Ức gà', 'should_eat', 'Ức gà cung cấp protein nạc, giúp duy trì khối cơ mà không làm tăng đường huyết đột ngột.'],
            ['Đái tháo đường type 2', 'E11', 'Yến mạch', 'should_eat', 'Yến mạch giàu beta-glucan, hỗ trợ kiểm soát đường huyết sau ăn.'],
            ['Đái tháo đường type 2', 'E11', 'Rau xanh luộc', 'should_eat', 'Rau xanh nhiều chất xơ, ít năng lượng, giúp kiểm soát khẩu phần tinh bột.'],
            ['Đái tháo đường type 2', 'E11', 'Khoai lang', 'should_eat', 'Khoai lang là nguồn tinh bột phức, phù hợp hơn cơm trắng khi dùng lượng vừa phải.'],
            ['Đái tháo đường type 2', 'E11', 'Cơm trắng', 'should_avoid', 'Cơm trắng có chỉ số đường huyết cao, dễ làm tăng glucose máu sau ăn.'],
            ['Đái tháo đường type 2', 'E11', 'Đồ ngọt / bánh kẹo nhiều đường', 'should_avoid', 'Đường tinh luyện làm tăng đường huyết nhanh và khó kiểm soát.'],

            ['Tăng huyết áp', 'I10', 'Cá hồi', 'should_eat', 'Cá hồi giàu omega-3, hỗ trợ sức khỏe tim mạch và kiểm soát huyết áp.'],
            ['Tăng huyết áp', 'I10', 'Rau xanh luộc', 'should_eat', 'Rau xanh cung cấp kali và magie, hỗ trợ điều hòa huyết áp.'],
            ['Tăng huyết áp', 'I10', 'Dưa muối / thực phẩm đóng hộp', 'should_avoid', 'Thực phẩm nhiều natri có thể làm tăng giữ nước và tăng huyết áp.'],
            ['Tăng huyết áp', 'I10', 'Thịt bò / nội tạng động vật', 'should_avoid', 'Chất béo bão hòa có thể làm tăng nguy cơ xơ vữa mạch máu.'],

            ['Gout', 'M10.9', 'Cam / ổi', 'should_eat', 'Vitamin C hỗ trợ đào thải acid uric qua đường tiết niệu.'],
            ['Gout', 'M10.9', 'Rau xanh luộc', 'should_eat', 'Rau xanh ít purin, giàu chất xơ, phù hợp với người tăng acid uric.'],
            ['Gout', 'M10.9', 'Thịt bò / nội tạng động vật', 'should_avoid', 'Nội tạng và thịt đỏ giàu purin, dễ làm tăng acid uric.'],

            ['Viêm dạ dày', 'K29', 'Bí đỏ / cháo loãng', 'should_eat', 'Món mềm, dễ tiêu, giúp giảm kích ứng niêm mạc dạ dày.'],
            ['Viêm dạ dày', 'K29', 'Sữa chua không đường', 'should_eat', 'Sữa chua không đường có thể hỗ trợ hệ vi sinh đường ruột nếu dung nạp tốt.'],
            ['Viêm dạ dày', 'K29', 'Nghệ / gừng', 'should_eat', 'Dùng lượng vừa phải có thể hỗ trợ tiêu hóa và giảm khó chịu.'],
            ['Viêm dạ dày', 'K29', 'Đồ ăn cay nóng', 'should_avoid', 'Đồ cay nóng dễ kích ứng niêm mạc và làm tăng đau rát thượng vị.'],
            ['Viêm dạ dày', 'K29', 'Nước đá / đồ uống lạnh', 'should_avoid', 'Đồ uống quá lạnh có thể làm tăng co thắt và khó chịu đường tiêu hóa.'],

            ['Thoái hóa khớp', 'M19.9', 'Nước hầm xương', 'should_eat', 'Bổ sung collagen và khoáng chất tự nhiên, hỗ trợ chế độ ăn cho xương khớp.'],
            ['Thoái hóa khớp', 'M19.9', 'Cá hồi', 'should_eat', 'Omega-3 trong cá hồi hỗ trợ giảm phản ứng viêm.'],
            ['Thoái hóa khớp', 'M19.9', 'Đồ ngọt / bánh kẹo nhiều đường', 'should_avoid', 'Đường tinh luyện có thể làm tăng phản ứng viêm hệ thống.'],

            ['Cúm A', 'J10.1', 'Súp gà / cháo hành tía tô', 'should_eat', 'Món lỏng ấm, dễ tiêu, giúp bổ sung năng lượng khi mệt mỏi.'],
            ['Cúm A', 'J10.1', 'Mật ong ấm', 'should_eat', 'Mật ong ấm có thể làm dịu họng và giảm ho khan.'],
            ['Cúm A', 'J10.1', 'Nước ấm', 'should_eat', 'Uống đủ nước ấm giúp làm loãng dịch tiết và tránh mất nước khi sốt.'],
            ['Cúm A', 'J10.1', 'Nước đá / đồ uống lạnh', 'should_avoid', 'Đồ lạnh có thể làm nặng triệu chứng đau họng hoặc ho.'],

            ['Cúm B', 'J11.1', 'Súp gà / cháo hành tía tô', 'should_eat', 'Món ấm, dễ tiêu, hỗ trợ phục hồi khi nhiễm cúm.'],
            ['Cúm B', 'J11.1', 'Mật ong ấm', 'should_eat', 'Hỗ trợ làm dịu họng và giảm kích thích ho.'],
            ['Cúm B', 'J11.1', 'Nước đá / đồ uống lạnh', 'should_avoid', 'Đồ lạnh có thể gây co mạch vùng họng và tăng khó chịu.'],

            ['COVID-19', 'U07.1', 'Trứng gà', 'should_eat', 'Trứng là nguồn protein hoàn chỉnh, hỗ trợ phục hồi và miễn dịch.'],
            ['COVID-19', 'U07.1', 'Cam / ổi', 'should_eat', 'Vitamin C hỗ trợ miễn dịch và tăng sức đề kháng.'],
            ['COVID-19', 'U07.1', 'Súp gà / cháo hành tía tô', 'should_eat', 'Món mềm, dễ ăn khi mệt mỏi hoặc đau họng.'],

            ['Viêm phổi', 'J18.9', 'Bông cải xanh / bắp cải', 'should_eat', 'Rau họ cải giàu chất chống oxy hóa, hỗ trợ đáp ứng viêm.'],
            ['Viêm phổi', 'J18.9', 'Cá hồi', 'should_eat', 'Omega-3 hỗ trợ kiểm soát phản ứng viêm.'],
            ['Viêm phổi', 'J18.9', 'Sữa nguyên kem béo', 'should_avoid', 'Sản phẩm sữa béo có thể làm tăng cảm giác đờm đặc ở một số người.'],
            ['Viêm phổi', 'J18.9', 'Đồ ăn chiên rán cứng', 'should_avoid', 'Đồ chiên rán khó tiêu, không phù hợp khi đang viêm nhiễm hô hấp.'],

            ['Viêm phế quản', 'J20.9', 'Mật ong ấm', 'should_eat', 'Mật ong ấm giúp làm dịu niêm mạc họng và giảm kích thích ho.'],
            ['Viêm phế quản', 'J20.9', 'Súp gà / cháo hành tía tô', 'should_eat', 'Món ấm, mềm, dễ tiêu và bổ sung nước.'],
            ['Viêm phế quản', 'J20.9', 'Sữa nguyên kem béo', 'should_avoid', 'Một số người có thể thấy đờm đặc hơn khi dùng sữa béo.'],
            ['Viêm phế quản', 'J20.9', 'Nước đá / đồ uống lạnh', 'should_avoid', 'Đồ lạnh có thể kích thích ho.'],

            ['Viêm họng', 'J02.9', 'Mật ong ấm', 'should_eat', 'Mật ong ấm giúp làm dịu niêm mạc họng.'],
            ['Viêm họng', 'J02.9', 'Súp gà / cháo hành tía tô', 'should_eat', 'Món mềm, ấm, dễ nuốt khi đau họng.'],
            ['Viêm họng', 'J02.9', 'Nước ấm', 'should_eat', 'Nước ấm giúp giảm khô rát họng.'],
            ['Viêm họng', 'J02.9', 'Nước đá / đồ uống lạnh', 'should_avoid', 'Đồ lạnh có thể làm tăng đau rát hoặc ho.'],
            ['Viêm họng', 'J02.9', 'Đồ ăn chiên rán cứng', 'should_avoid', 'Đồ cứng dễ cọ xát vùng họng đang viêm.'],

            ['Viêm amidan', 'J03.9', 'Mật ong ấm', 'should_eat', 'Hỗ trợ làm dịu vùng họng và amidan đang sưng.'],
            ['Viêm amidan', 'J03.9', 'Súp gà / cháo hành tía tô', 'should_eat', 'Món mềm, dễ nuốt, phù hợp khi đau họng.'],
            ['Viêm amidan', 'J03.9', 'Đồ ăn chiên rán cứng', 'should_avoid', 'Đồ cứng có thể gây đau và kích ứng amidan.'],
            ['Viêm amidan', 'J03.9', 'Nước đá / đồ uống lạnh', 'should_avoid', 'Đồ lạnh có thể làm tăng khó chịu vùng họng.'],

            // Tên bệnh thường gặp trong form tạo hồ sơ hoặc dữ liệu hiện có
            ['Tiểu đường', 'E11', 'Ức gà', 'should_eat', 'Protein nạc giúp no lâu và ít ảnh hưởng đến đường huyết.'],
            ['Tiểu đường', 'E11', 'Yến mạch', 'should_eat', 'Giàu chất xơ hòa tan, hỗ trợ kiểm soát đường huyết.'],
            ['Tiểu đường', 'E11', 'Cơm trắng', 'should_avoid', 'Nên hạn chế lượng cơm trắng vì chỉ số đường huyết cao.'],
            ['Tiểu đường', 'E11', 'Đồ ngọt / bánh kẹo nhiều đường', 'should_avoid', 'Đồ ngọt làm tăng đường huyết nhanh.'],

            ['Cao huyết áp', 'I10', 'Cá hồi', 'should_eat', 'Cá béo giàu omega-3, tốt cho tim mạch.'],
            ['Cao huyết áp', 'I10', 'Rau xanh luộc', 'should_eat', 'Rau xanh giàu kali và chất xơ.'],
            ['Cao huyết áp', 'I10', 'Dưa muối / thực phẩm đóng hộp', 'should_avoid', 'Nên hạn chế thực phẩm nhiều muối.'],

            ['Đau dạ dày', 'K29', 'Bí đỏ / cháo loãng', 'should_eat', 'Món mềm, dễ tiêu và ít kích ứng dạ dày.'],
            ['Đau dạ dày', 'K29', 'Sữa chua không đường', 'should_eat', 'Có thể hỗ trợ tiêu hóa nếu người bệnh dung nạp tốt.'],
            ['Đau dạ dày', 'K29', 'Đồ ăn cay nóng', 'should_avoid', 'Đồ cay nóng dễ làm tăng đau rát thượng vị.'],

            ['Cảm cúm', 'J11.1', 'Súp gà / cháo hành tía tô', 'should_eat', 'Món ấm, mềm, dễ tiêu khi cảm cúm.'],
            ['Cảm cúm', 'J11.1', 'Cam / ổi', 'should_eat', 'Bổ sung vitamin C hỗ trợ miễn dịch.'],
            ['Cảm cúm', 'J11.1', 'Nước đá / đồ uống lạnh', 'should_avoid', 'Đồ lạnh có thể làm tăng ho và đau họng.'],

            ['Hen suyễn', 'J45', 'Cá hồi', 'should_eat', 'Omega-3 có thể hỗ trợ kiểm soát phản ứng viêm.'],
            ['Hen suyễn', 'J45', 'Bông cải xanh / bắp cải', 'should_eat', 'Rau giàu chất chống oxy hóa, hỗ trợ sức khỏe hô hấp.'],
            ['Hen suyễn', 'J45', 'Sữa nguyên kem béo', 'should_avoid', 'Một số người hen có thể nhạy cảm với sản phẩm sữa béo.'],
            ['Hen suyễn', 'J45', 'Nước đá / đồ uống lạnh', 'should_avoid', 'Đồ lạnh có thể kích thích ho hoặc co thắt phế quản ở một số người.'],

            ['Viêm khớp', '44', 'Cá hồi', 'should_eat', 'Omega-3 hỗ trợ giảm phản ứng viêm khớp.'],
            ['Viêm khớp', '44', 'Rau xanh luộc', 'should_eat', 'Rau xanh giàu chất chống oxy hóa và chất xơ.'],
            ['Viêm khớp', '44', 'Hạt hạnh nhân', 'should_eat', 'Hạt giàu vitamin E và chất béo tốt, phù hợp dùng lượng vừa phải.'],
            ['Viêm khớp', '44', 'Đồ ngọt / bánh kẹo nhiều đường', 'should_avoid', 'Đường tinh luyện có thể làm tăng phản ứng viêm.'],
            ['Viêm khớp', '44', 'Đồ ăn chiên rán cứng', 'should_avoid', 'Đồ chiên rán nhiều dầu mỡ có thể làm nặng tình trạng viêm.'],

            ['Viêm da', null, 'Cam / ổi', 'should_eat', 'Vitamin C hỗ trợ tổng hợp collagen và phục hồi da.'],
            ['Viêm da', null, 'Rau xanh luộc', 'should_eat', 'Rau xanh cung cấp vitamin, khoáng chất và chất chống oxy hóa.'],
            ['Viêm da', null, 'Hạt hạnh nhân', 'should_eat', 'Vitamin E và chất béo tốt hỗ trợ hàng rào bảo vệ da.'],
            ['Viêm da', null, 'Đồ ngọt / bánh kẹo nhiều đường', 'should_avoid', 'Đồ ngọt có thể làm tăng phản ứng viêm ở một số cơ địa.'],
            ['Viêm da', null, 'Đồ ăn cay nóng', 'should_avoid', 'Đồ cay nóng có thể làm tăng ngứa hoặc kích ứng da ở một số người.'],

            ['Đau đầu', null, 'Nước ấm', 'should_eat', 'Bổ sung nước giúp tránh đau đầu liên quan đến mất nước.'],
            ['Đau đầu', null, 'Cam / ổi', 'should_eat', 'Trái cây tươi bổ sung vitamin và nước.'],
            ['Đau đầu', null, 'Nước đá / đồ uống lạnh', 'should_avoid', 'Đồ uống quá lạnh có thể kích thích đau đầu ở một số người.'],
        ];

        foreach ($rules as [$diseaseName, $icdCode, $foodName, $type, $reason]) {
            $foodId = $foodIds[$foodName] ?? null;

            if (! $foodId) {
                continue;
            }

            DB::table('disease_nutrition_rules')->updateOrInsert(
                [
                    'disease_name' => $diseaseName,
                    'food_id' => $foodId,
                ],
                [
                    'icd_code' => $icdCode,
                    'recommendation_type' => $type,
                    'reason' => $reason,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
