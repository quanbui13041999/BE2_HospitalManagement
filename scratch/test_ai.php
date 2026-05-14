<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\GeminiChatService;
use Illuminate\Support\Facades\Config;

// Mock config if needed, but it should read from .env
$apiKey = env('GEMINI_API_KEY');
echo "Testing with API Key: " . substr($apiKey, 0, 10) . "...\n";

$service = new GeminiChatService();
try {
    // We need a dummy room ID, let's assume 1 exists or just pass any int 
    // since the service only uses it to fetch history
    $reply = $service->generateReply(1, "Xin chào, bạn là ai?");
    echo "AI Reply: " . $reply . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
