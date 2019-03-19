<?php

require_once "vendor/autoload.php";

use Jttp\Jttp;

$response = (new Jttp)
    ->url("https://httpbin.org/get")
    ->get();

if (!$response->isOk()) {
    echo "Error: Can't get data\n";
    return;
}

echo "Status: " . $response->status() . "\n\n";
echo "Headers: " . $response->headers() . "\n\n";
echo "Body: " . $response->body() . "\n\n";
echo "Json: ";
try {
    var_dump($response->json());
} catch (\Jttp\JsonException $e) {
    echo "Json decode error: " . $e->getMessage() . "\n";
    return;
}