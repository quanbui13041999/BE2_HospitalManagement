<?php

namespace App\Services;

class HealthRiskService
{
    public function analyze(array $data): array
    {
        $warnings = $this->detectWarnings($data);

        return [
            'risk_level'    => $this->calculateRiskLevel($warnings),
            'risk_warnings' => $warnings,
        ];
    }

    public function detectWarnings(array $data): array
    {
        $warnings = [];

        $checks = [
            'systolic' => function ($v) {
                if ($v > 180) return ['danger',  'bi-heart-pulse-fill', "Huyết áp tâm thu rất cao ({$v} mmHg) - Nguy hiểm!"];
                if ($v > 140) return ['warning', 'bi-heart-pulse',      "Huyết áp tâm thu cao ({$v} mmHg) - Cần theo dõi."];
                return null;
            },
            'diastolic' => function ($v) {
                if ($v > 120) return ['danger',  'bi-heart-pulse-fill', "Huyết áp tâm trương rất cao ({$v} mmHg) - Nguy hiểm!"];
                if ($v > 90)  return ['warning', 'bi-heart-pulse',      "Huyết áp tâm trương cao ({$v} mmHg) - Cần theo dõi."];
                return null;
            },
            'spo2' => function ($v) {
                if ($v < 90) return ['danger',  'bi-lungs-fill', "Nồng độ oxy thấp nguy hiểm ({$v}%) - Cần cấp cứu ngay!"];
                if ($v < 95) return ['warning', 'bi-lungs',      "Nồng độ oxy thấp ({$v}%) - Cần theo dõi."];
                return null;
            },
            'heart_rate' => function ($v) {
                if ($v < 40 || $v > 180) return ['danger',  'bi-activity', "Nhịp tim bất thường ({$v} bpm) - Nguy hiểm!"];
                if ($v < 50 || $v > 120) return ['warning', 'bi-activity', "Nhịp tim bất thường ({$v} bpm) - Cần theo dõi."];
                return null;
            },
            'blood_sugar' => function ($v) {
                if ($v > 300) return ['danger',  'bi-droplet-fill', "Đường huyết quá cao ({$v} mg/dL) - Nguy hiểm!"];
                if ($v > 200) return ['warning', 'bi-droplet',      "Đường huyết cao ({$v} mg/dL) - Cần theo dõi."];
                if ($v < 70)  return ['warning', 'bi-droplet-half', "Đường huyết thấp ({$v} mg/dL) - Cần ăn ngay!"];
                return null;
            },
        ];

        foreach ($checks as $field => $fn) {
            if (!isset($data[$field])) continue;
            $result = $fn((int) $data[$field]);
            if ($result) {
                [$level, $icon, $message] = $result;
                $warnings[] = compact('field', 'level', 'icon', 'message');
            }
        }

        return $warnings;
    }

    public function calculateRiskLevel(array $warnings): string
    {
        if (empty($warnings)) return 'normal';
        foreach ($warnings as $w) {
            if ($w['level'] === 'danger') return 'danger';
        }
        return 'warning';
    }
}
