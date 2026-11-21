<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeviceSeeder extends Seeder
{
    public function run(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('device_types') || ! DB::getSchemaBuilder()->hasTable('devices')) {
            $this->command?->warn('Bỏ qua DeviceSeeder vì chưa có bảng device_types hoặc devices.');
            return;
        }

        $now = now();

        $types = [
            ['name' => 'Thiết bị chẩn đoán hình ảnh', 'description' => 'Máy siêu âm, X-quang và thiết bị hỗ trợ chẩn đoán hình ảnh.'],
            ['name' => 'Thiết bị xét nghiệm', 'description' => 'Máy phân tích mẫu máu, nước tiểu và sinh hóa.'],
            ['name' => 'Thiết bị cấp cứu', 'description' => 'Thiết bị dùng cho cấp cứu và hồi sức ban đầu.'],
            ['name' => 'Thiết bị phòng mổ', 'description' => 'Thiết bị phục vụ phẫu thuật và thủ thuật.'],
            ['name' => 'Thiết bị theo dõi bệnh nhân', 'description' => 'Thiết bị đo và giám sát dấu hiệu sinh tồn.'],
            ['name' => 'Thiết bị vật lý trị liệu', 'description' => 'Thiết bị phục hồi chức năng và vật lý trị liệu.'],
        ];

        foreach ($types as $type) {
            $exists = DB::table('device_types')->where('name', $type['name'])->exists();

            if ($exists) {
                DB::table('device_types')
                    ->where('name', $type['name'])
                    ->update([
                        'description' => $type['description'],
                        'updated_at' => $now,
                    ]);
            } else {
                DB::table('device_types')->insert([
                    'name' => $type['name'],
                    'description' => $type['description'],
                    'lock_version' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $typeIds = DB::table('device_types')->pluck('id', 'name');

        $devices = [
            ['code' => 'IMG-US-001', 'name' => 'Máy siêu âm Doppler màu', 'type' => 'Thiết bị chẩn đoán hình ảnh', 'status' => 'active', 'purchase_date' => '2024-03-15'],
            ['code' => 'IMG-XR-001', 'name' => 'Máy chụp X-quang kỹ thuật số', 'type' => 'Thiết bị chẩn đoán hình ảnh', 'status' => 'maintenance', 'purchase_date' => '2023-09-20'],
            ['code' => 'LAB-BC-001', 'name' => 'Máy xét nghiệm huyết học tự động', 'type' => 'Thiết bị xét nghiệm', 'status' => 'active', 'purchase_date' => '2024-01-10'],
            ['code' => 'LAB-BIO-001', 'name' => 'Máy phân tích sinh hóa bán tự động', 'type' => 'Thiết bị xét nghiệm', 'status' => 'active', 'purchase_date' => '2022-11-05'],
            ['code' => 'ER-DEF-001', 'name' => 'Máy sốc điện tim', 'type' => 'Thiết bị cấp cứu', 'status' => 'active', 'purchase_date' => '2024-06-01'],
            ['code' => 'ER-VEN-001', 'name' => 'Máy thở cấp cứu', 'type' => 'Thiết bị cấp cứu', 'status' => 'maintenance', 'purchase_date' => '2023-04-22'],
            ['code' => 'OR-LAMP-001', 'name' => 'Đèn mổ treo trần', 'type' => 'Thiết bị phòng mổ', 'status' => 'active', 'purchase_date' => '2023-12-12'],
            ['code' => 'OR-PUMP-001', 'name' => 'Bơm tiêm điện phòng mổ', 'type' => 'Thiết bị phòng mổ', 'status' => 'active', 'purchase_date' => '2024-02-18'],
            ['code' => 'MON-VS-001', 'name' => 'Monitor theo dõi bệnh nhân năm thông số', 'type' => 'Thiết bị theo dõi bệnh nhân', 'status' => 'active', 'purchase_date' => '2025-01-08'],
            ['code' => 'MON-SPO2-001', 'name' => 'Máy đo nồng độ oxy máu cầm tay', 'type' => 'Thiết bị theo dõi bệnh nhân', 'status' => 'broken', 'purchase_date' => '2022-08-30'],
            ['code' => 'REHAB-US-001', 'name' => 'Máy siêu âm trị liệu', 'type' => 'Thiết bị vật lý trị liệu', 'status' => 'active', 'purchase_date' => '2024-05-14'],
            ['code' => 'REHAB-LASER-001', 'name' => 'Máy laser trị liệu công suất thấp', 'type' => 'Thiết bị vật lý trị liệu', 'status' => 'active', 'purchase_date' => '2024-10-09'],
        ];

        foreach ($devices as $device) {
            $typeId = $typeIds[$device['type']] ?? null;

            if (! $typeId) {
                continue;
            }

            $exists = DB::table('devices')->where('code', $device['code'])->exists();

            if ($exists) {
                DB::table('devices')
                    ->where('code', $device['code'])
                    ->update([
                        'name' => $device['name'],
                        'device_type_id' => $typeId,
                        'status' => $device['status'],
                        'purchase_date' => $device['purchase_date'],
                        'updated_at' => $now,
                    ]);
            } else {
                DB::table('devices')->insert([
                    'code' => $device['code'],
                    'name' => $device['name'],
                    'device_type_id' => $typeId,
                    'status' => $device['status'],
                    'purchase_date' => $device['purchase_date'],
                    'lock_version' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $this->command?->info('Đã seed dữ liệu mẫu thiết bị.');
    }
}
