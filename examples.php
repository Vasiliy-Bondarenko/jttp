<?php

require_once "vendor/autoload.php";

use Jttp\Jttp;
use Jttp\JttpException;

try {
    $response = (new Jttp)
        ->url("https://httpbin.org/get")
        ->get();

    echo "Status:   " . $response->status() . "\n\n";
    echo "Headers:  " . var_export($response->headers(), true) . "\n\n";
    echo "Body:     " . $response->body() . "\n\n";
    echo "Json:     " . var_export($response->json(), true) . "\n\n";
} catch (JttpException $e) {
    // it will catch all errors here including non 2xx response codes and json_decode errors
    echo "Error: {$e->getMessage()}\n";
}