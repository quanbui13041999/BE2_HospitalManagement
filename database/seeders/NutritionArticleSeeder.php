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
                'title' => 'Chế độ ăn cho người tiểu đường',
                'slug' => 'che-do-an-cho-nguoi-tieu-duong',
                'content' => 'Người bệnh tiểu đường nên hạn chế đường, tăng cường rau xanh, ngũ cốc nguyên cám và thực phẩm giàu chất xơ.',
                'target_disease' => 'Tiểu đường',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'doctor_id' => 11,
                'title' => 'Dinh dưỡng cho người cao huyết áp',
                'slug' => 'dinh-duong-cho-nguoi-cao-huyet-ap',
                'content' => 'Người cao huyết áp nên giảm muối, hạn chế thực phẩm nhiều dầu mỡ và ăn nhiều trái cây.',
                'target_disease' => 'Cao huyết áp',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'doctor_id' => 11,
                'title' => 'Thực phẩm tốt cho tim mạch',
                'slug' => 'thuc-pham-tot-cho-tim-mach',
                'content' => 'Các loại cá béo, rau xanh và hạt dinh dưỡng rất tốt cho sức khỏe tim mạch.',
                'target_disease' => 'Tim mạch',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'doctor_id' => null,
                'title' => '5 nguyên tắc ăn uống lành mạnh',
                'slug' => '5-nguyen-tac-an-uong-lanh-manh',
                'content' => 'Ăn đủ chất, uống đủ nước, hạn chế thức ăn nhanh và duy trì vận động hàng ngày.',
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
