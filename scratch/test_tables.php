<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $tables = DB::select('SHOW TABLES');
    foreach($tables as $table) {
        $tableName = array_values((array)$table)[0];
        try {
            $count = DB::table($tableName)->count();
            echo "$tableName: $count\n";
        } catch (\Exception $e) {
            echo "$tableName: Error - " . $e->getMessage() . "\n";
        }
    }
} catch (\Exception $e) {
    echo "General Error: " . $e->getMessage() . "\n";
}
