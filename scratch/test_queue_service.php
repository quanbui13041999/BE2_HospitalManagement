<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\QueueService;
use App\Models\QueueTicket;
use App\Models\QueueCounter;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

// Mock login user (e.g. Receptionist/Admin)
$admin = \App\Models\User::where('role_id', 1)->first();
Auth::login($admin);

// Clean up today's tickets so we start clean for the test
QueueTicket::where('schedule_id', 399)->delete();
QueueCounter::where('schedule_id', 399)->delete();

$service = new QueueService();

echo "1. Testing findPatient for Appointment #36...\n";
$find1 = $service->findPatient('36');
echo "Found: " . ($find1['found'] ? 'YES' : 'NO') . "\n";
if ($find1['found']) {
    echo "Type: {$find1['type']}\n";
    echo "Patient: {$find1['data']->user->full_name}\n";
}

echo "\n2. Testing checkin for Appointment #36...\n";
$ticket1 = $service->checkin([
    'schedule_id' => 399,
    'appointment_id' => 36,
    'user_id' => 23,
    'patient_name' => 'Bệnh nhân test 36',
    'priority' => 'normal',
    'patient_phone' => '0999999999',
    'notes' => 'Test online appointment checkin'
]);
echo "Ticket created! Queue Number: #{$ticket1->queue_number}, Priority: {$ticket1->priority}\n";

echo "\n3. Testing checkin for multiple walk-ins with different priorities...\n";
// Normal walk-in
$ticket2 = $service->checkin([
    'schedule_id' => 399,
    'patient_name' => 'Bệnh nhân Thường 1',
    'priority' => 'normal',
    'notes' => 'Walk-in normal'
]);
echo "Walk-in 1: #{$ticket2->queue_number}, Priority: {$ticket2->priority}\n";

// Elderly walk-in
$ticket3 = $service->checkin([
    'schedule_id' => 399,
    'patient_name' => 'Bệnh nhân Cao Tuổi 1',
    'priority' => 'elderly',
    'notes' => 'Walk-in elderly'
]);
echo "Walk-in 2: #{$ticket3->queue_number}, Priority: {$ticket3->priority}\n";

// Emergency walk-in
$ticket4 = $service->checkin([
    'schedule_id' => 399,
    'patient_name' => 'Bệnh nhân Cấp Cứu 1',
    'priority' => 'emergency',
    'notes' => 'Walk-in emergency'
]);
echo "Walk-in 3: #{$ticket4->queue_number}, Priority: {$ticket4->priority}\n";

// Disabled walk-in
$ticket5 = $service->checkin([
    'schedule_id' => 399,
    'patient_name' => 'Bệnh nhân Khuyết Tật 1',
    'priority' => 'disabled',
    'notes' => 'Walk-in disabled'
]);
echo "Walk-in 4: #{$ticket5->queue_number}, Priority: {$ticket5->priority}\n";

// Normal walk-in 2
$ticket6 = $service->checkin([
    'schedule_id' => 399,
    'patient_name' => 'Bệnh nhân Thường 2',
    'priority' => 'normal',
    'notes' => 'Walk-in normal 2'
]);
echo "Walk-in 5: #{$ticket6->queue_number}, Priority: {$ticket6->priority}\n";

echo "\n4. Verifying queue ordering (Emergency > Disabled > Elderly > Normal)...\n";
$snapshot = $service->getQueueSnapshot(399);
echo "Waiting list count: " . count($snapshot['waiting']) . "\n";
$order = 1;
foreach ($snapshot['waiting'] as $t) {
    echo "Position {$order}: Ticket #{$t->queue_number} - Name: {$t->patient_name} - Priority: {$t->priority}\n";
    $order++;
}

echo "\n5. Calling next patient (expected: Emergency #4)...\n";
$called = $service->callNext(399);
if ($called) {
    echo "Called ticket: #{$called->queue_number} - {$called->patient_name} - Status: {$called->status}\n";
} else {
    echo "No patient called!\n";
}

echo "\n6. Starting exam on Called ticket...\n";
$started = $service->startExam($called->ticket_id);
echo "Ticket #{$started->queue_number} status updated to: {$started->status}\n";

echo "\n7. Completing exam on Called ticket...\n";
$completed = $service->complete($started->ticket_id);
echo "Ticket #{$completed->queue_number} status updated to: {$completed->status}\n";

echo "\n8. Verifying queue snapshot after completion...\n";
$snapshot2 = $service->getQueueSnapshot(399);
echo "Waiting list count now: " . count($snapshot2['waiting']) . "\n";
$order = 1;
foreach ($snapshot2['waiting'] as $t) {
    echo "Position {$order}: Ticket #{$t->queue_number} - Name: {$t->patient_name} - Wait est: {$t->est_wait_minutes} mins\n";
    $order++;
}

echo "\n9. Calling next patient (expected: Disabled #5)...\n";
$called2 = $service->callNext(399);
if ($called2) {
    echo "Called ticket: #{$called2->queue_number} - {$called2->patient_name} - Status: {$called2->status}\n";
} else {
    echo "No patient called!\n";
}

echo "\n10. Skipping patient #{$called2->queue_number}...\n";
$skipped = $service->skip($called2->ticket_id, "Bệnh nhân không có mặt");
echo "Ticket #{$skipped->queue_number} status updated to: {$skipped->status}, Notes: {$skipped->notes}\n";

// Clean up
QueueTicket::where('schedule_id', 399)->delete();
QueueCounter::where('schedule_id', 399)->delete();
Appointment::where('appointment_id', 36)->update(['status' => 'Chờ xác nhận']);
echo "\n--- TEST COMPLETED SUCCESSFULLY ---\n";
