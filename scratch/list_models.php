<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$apiKey = env('GEMINI_API_KEY');
$response = Http::withoutVerifying()->get("https://generativelanguage.googleapis.com/v1beta/models?key=$apiKey");

if ($response->successful()) {
    $models = $response->json();
    foreach ($models['models'] as $model) {
        echo $model['name'] . " - " . implode(',', $model['supportedGenerationMethods']) . "\n";
    }
} else {
    echo "Error listing models: " . $response->body() . "\n";
}
