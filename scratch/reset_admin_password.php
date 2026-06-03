<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$admin = User::where('email', 'admin@email.com')->first();
if ($admin) {
    $admin->password = Hash::make('admin123');
    $admin->save();
    echo "Successfully updated password for admin@email.com to 'admin123'\n";
} else {
    echo "admin@email.com not found!\n";
}
