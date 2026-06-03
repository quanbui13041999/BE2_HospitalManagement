<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DoctorScheduleAvailabilitySeeder extends Seeder
{
    private const DAYS_TO_SEED = 30;

    private const SHIFTS = [
        [
            'start_time' => '08:00:00',
            'end_time' => '12:00:00',
            'max_slot' => 8,
            'note' => 'Ca sáng',
        ],
        [
            'start_time' => '13:30:00',
            'end_time' => '17:00:00',
            'max_slot' => 7,
            'note' => 'Ca chiều',
        ],
    ];

    public function run(): void
    {
        if (
            ! Schema::hasTable('doctorschedules')
            || ! Schema::hasTable('doctors')
            || ! Schema::hasTable('rooms')
        ) {
            return;
        }

        $doctors = DB::table('doctors')
            ->where('status', 1)
            ->orderBy('doctor_id')
            ->get(['doctor_id', 'department_id']);

        if ($doctors->isEmpty()) {
            return;
        }

        $roomsByDepartment = DB::table('rooms')
            ->whereIn('status', ['Trống', 'Hoạt động', 'active'])
            ->orderBy('room_id')
            ->get(['room_id', 'department_id'])
            ->groupBy('department_id');

        $fallbackRoomId = DB::table('rooms')->orderBy('room_id')->value('room_id');
        $today = Carbon::today();
        $rows = [];

        foreach ($doctors as $doctor) {
            $departmentRooms = $roomsByDepartment->get($doctor->department_id);
            $roomId = $departmentRooms?->first()?->room_id ?? $fallbackRoomId;

            for ($day = 0; $day < self::DAYS_TO_SEED; $day++) {
                $workDate = $today->copy()->addDays($day)->toDateString();

                foreach (self::SHIFTS as $shift) {
                    $rows[] = [
                        'doctor_id' => $doctor->doctor_id,
                        'room_id' => $roomId,
                        'work_date' => $workDate,
                        'start_time' => $shift['start_time'],
                        'end_time' => $shift['end_time'],
                        'slot_duration' => 30,
                        'max_slot' => $shift['max_slot'],
                        'status' => 'Hoạt động',
                        'note' => $shift['note'],
                    ];
                }
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('doctorschedules')->insertOrIgnore($chunk);
        }
    }
}
