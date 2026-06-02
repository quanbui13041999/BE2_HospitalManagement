<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NutritionArticleSeeder extends Seeder
{
    public function run(): void
    {
        $articles = [
            [
                'doctor_id' => 11,
                'title' => 'Chế độ dinh dưỡng hỗ trợ phục hồi khi mắc Cúm A',
                'slug' => 'che-do-dinh-duong-ho-tro-phuc-hoi-khi-mac-cum-a',
                'content' => 'Người bệnh Cúm A cần bổ sung nhiều nước, các loại súp lỏng dễ tiêu, vitamin C và kẽm từ trái cây họ cam quýt để tăng cường hệ miễn dịch.',
                'target_disease' => 'Cúm A',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'doctor_id' => 11,
                'title' => 'Người mắc Cúm B nên ăn gì để giảm mệt mỏi?',
                'slug' => 'nguoi-mac-cum-b-nen-an-gi-de-giam-met-moi',
                'content' => 'Ưu tiên các món ăn giàu dinh dưỡng và dễ nuốt như cháo gà, uống nhiều nước ấm, nước gừng rây để làm ấm cơ thể và giảm cảm giác chán ăn.',
                'target_disease' => 'Cúm B',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'doctor_id' => 11,
                'title' => 'Dinh dưỡng phục hồi sức khỏe hậu COVID-19',
                'slug' => 'dinh-duong-phuc-hoi-suc-khoe-hau-covid-19',
                'content' => 'Bổ sung đầy đủ chất đạm từ thịt, cá, trứng, sữa và các chất béo lành mạnh để tái tạo các mô tổn thương, kết hợp bổ sung Vitamin D3.',
                'target_disease' => 'COVID-19',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'doctor_id' => 11,
                'title' => 'Chế độ ăn giảm tải áp lực cho phổi khi bị Viêm phổi',
                'slug' => 'che-do-an-giam-tai-ap-luc-cho-phoi-khi-bi-viem-phoi',
                'content' => 'Hạn chế thực phẩm nhiều carbohydrate sinh nhiều CO2, tăng cường chất béo omega-3 chống viêm và chia nhỏ bữa ăn để tránh làm khó thở.',
                'target_disease' => 'Viêm phổi',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'doctor_id' => 11,
                'title' => 'Thực phẩm giúp long đờm và giảm ho do Viêm phế quản',
                'slug' => 'thuc-pham-giup-long-dom-va-giam-ho-do-viem-phe-quan',
                'content' => 'Uống nhiều nước ấm, sử dụng mật ong, tỏi, gừng và hạn chế các món ăn nhiều dầu mỡ, đồ uống lạnh gây kích ứng niêm mạc phế quản.',
                'target_disease' => 'Viêm phế quản',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'doctor_id' => 11,
                'title' => 'Món ăn mềm dịu giúp làm mát và dịu cổ họng khi Viêm họng',
                'slug' => 'mon-an-mem-diu-giup-lam-mat-va-diu-co-hong-khi-viem-hong',
                'content' => 'Tránh thức ăn cứng, giòn, cay nóng. Nên ăn các loại súp, canh hầm và ngậm nước muối ấm hoặc trà hoa cúc mật ong để sát khuẩn.',
                'target_disease' => 'Viêm họng',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'doctor_id' => 11,
                'title' => 'Cách chọn thực phẩm dễ nuốt khi bị Viêm amidan cấp',
                'slug' => 'cach-chon-thuc-pham-de-nuot-khi-bi-viem-amidan-cap',
                'content' => 'Nên ăn đồ ăn xay nhuyễn, để nguội hoặc ấm vừa phải để không kích ứng vùng amidan đang sưng tấy. Tránh các loại nước ép có tính acid cao như chanh, khế.',
                'target_disease' => 'Viêm amidan',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'doctor_id' => 11,
                'title' => 'Chế độ ăn bảo vệ niêm mạc cho người Viêm dạ dày',
                'slug' => 'che-do-an-bao-ve-niem-mac-cho-nguoi-viem-da-day',
                'content' => 'Tránh bỏ bữa, hạn chế đồ chua, cay, bia rượu và chất kích thích. Ăn các thực phẩm có tính bọc niêm mạc tốt như bánh mì, khoai tây, nghệ và mật ong.',
                'target_disease' => 'Viêm dạ dày',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'doctor_id' => 11,
                'title' => 'Dinh dưỡng chuẩn DASH cho bệnh nhân Tăng huyết áp',
                'slug' => 'dinh-duong-chuan-dash-cho-benh-nhan-tang-huyet-ap',
                'content' => 'Thực hiện nghiêm ngặt chế độ ăn giảm muối (dưới 5g/ngày), tăng cường bổ sung Kali, Canxi từ rau xanh, trái cây và sữa không đường tách béo.',
                'target_disease' => 'Tăng huyết áp',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'doctor_id' => 11,
                'title' => 'Kiểm soát đường huyết hiệu quả cho người Đái tháo đường type 2',
                'slug' => 'kiem-soat-duong-huyet-hieu-qua-cho-nguoi-dai-thao-duong-type-2',
                'content' => 'Hạn chế tinh bột tinh chế, ưu tiên thực phẩm có chỉ số GI thấp như gạo lứt, yến mạch, ăn nhiều rau xanh trước bữa chính để làm chậm hấp thu đường.',
                'target_disease' => 'Đái tháo đường type 2',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'doctor_id' => 11,
                'title' => 'Chế độ ăn giảm Acid Uric phòng ngừa tái phát bệnh Gout',
                'slug' => 'che-do-an-giam-acid-uric-phong-ngua-tai-phat-benh-gout',
                'content' => 'Kiêng tuyệt đối nội tạng động vật, hải sản, thịt đỏ và bia rượu. Uống nhiều nước (từ 2-2.5 lít/ngày) để tăng cường đào thải acid uric qua thận.',
                'target_disease' => 'Gout',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'doctor_id' => 11,
                'title' => 'Thực phẩm hỗ trợ bôi trơn sụn khớp, giảm Thoái hóa khớp',
                'slug' => 'thuc-pham-ho-tro-boi-tron-sun-khop-giam-thoai-hoa-khop',
                'content' => 'Bổ sung thực phẩm giàu Omega-3 (cá hồi, quả óc chó), các nguồn Collagen tự nhiên từ nước hầm xương và vitamin C giúp tổng hợp chất nền sụn khớp.',
                'target_disease' => 'Thoái hóa khớp',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'doctor_id' => null,
                'title' => '5 nguyên tắc ăn uống lành mạnh để nâng cao sức khỏe tổng thể',
                'slug' => '5-nguyen-tac-an-uong-lanh-manh-de-nang-cao-suc-khoe-tong-the',
                'content' => 'Ăn đủ 4 nhóm chất, cân đối năng lượng nạp vào và tiêu hao, hạn chế thực phẩm chế biến sẵn, cắt giảm đường muối và uống đủ nước mỗi ngày.',
                'target_disease' => null,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($articles as $article) {
            DB::table('nutrition_articles')->updateOrInsert(
                ['slug' => $article['slug']],
                $article
            );
        }
    }
}
