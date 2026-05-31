<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Appointment;

echo "--- ROLES & USERS COUNT ---\n";
foreach (\DB::table('roles')->get() as $role) {
    $count = User::where('role_id', $role->role_id)->count();
    echo "Role #{$role->role_id} ({$role->role_name}): {$count} users\n";
}

echo "\n--- DOCTORS COUNT ---\n";
echo "Doctors: " . Doctor::count() . "\n";

echo "\n--- DOCTOR SCHEDULES FOR TODAY (" . today()->toDateString() . ") ---\n";
$schedules = DoctorSchedule::with('doctor')->whereDate('work_date', today())->get();
if ($schedules->isEmpty()) {
    echo "No doctor schedules for today.\n";
    // Check other days
    $latest = DoctorSchedule::latest('work_date')->first();
    if ($latest) {
        echo "Latest schedule is on: {$latest->work_date}\n";
    } else {
        echo "No doctor schedules in database at all.\n";
    }
} else {
    foreach ($schedules as $s) {
        echo "Schedule #{$s->schedule_id}: Doctor: {$s->doctor->full_name}, Time: {$s->start_time} - {$s->end_time}, Status: {$s->status}\n";
    }
}

echo "\n--- APPOINTMENTS FOR TODAY ---\n";
$appointments = Appointment::whereDate('appointment_time', today())->get();
echo "Today's appointments count: " . $appointments->count() . "\n";
foreach ($appointments as $app) {
    echo "App #{$app->appointment_id}: User: {$app->user_id}, Sched: {$app->schedule_id}, Status: {$app->status}\n";
}
