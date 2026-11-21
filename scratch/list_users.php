<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "ADMINS:\n";
foreach (User::where('role_id', 1)->get(['user_id', 'full_name', 'email']) as $u) {
    echo "- ID: {$u->user_id}, Name: {$u->full_name}, Email: {$u->email}\n";
}

echo "PATIENTS:\n";
foreach (User::where('role_id', 3)->limit(5)->get(['user_id', 'full_name', 'email']) as $u) {
    echo "- ID: {$u->user_id}, Name: {$u->full_name}, Email: {$u->email}\n";
}
