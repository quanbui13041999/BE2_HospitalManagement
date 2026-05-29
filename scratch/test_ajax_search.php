<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\Admin\PatientSearchController;

$request = Request::create('/admin/patients/search/results', 'GET', [
    'page' => 1,
    'per_page' => 12,
    'sort_by' => 'created_at',
    'sort_dir' => 'desc'
]);

// Thiết lập header AJAX để controller xử lý trả JSON
$request->headers->set('X-Requested-With', 'XMLHttpRequest');

$controller = new PatientSearchController();

try {
    echo "Sending AJAX Request to PatientSearchController@search...\n";
    $response = $controller->search($request);
    
    echo "Response status: " . $response->getStatusCode() . "\n";
    $content = $response->getContent();
    
    // Nếu status không phải 200, in ra body lỗi
    if ($response->getStatusCode() !== 200) {
        echo "Error Response Body:\n" . substr($content, 0, 1000) . "\n";
    } else {
        $data = json_decode($content, true);
        echo "Response JSON decode successful!\n";
        echo "Total patients: " . ($data['total'] ?? 'N/A') . "\n";
        echo "HTML Content preview:\n" . substr($data['html'] ?? '', 0, 500) . "\n";
    }
} catch (\Throwable $e) {
    echo "CRITICAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
