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
    // it will catch all errors here including non 2xx response codes and json_decode errors, too many redirects, etc
    echo "Error: {$e->getMessage()}\n";
}

// more complex
try {
    // prepare http client
    $client = (new Jttp)
        ->retries(2)
        ->redirects(5)
        ->logToFile("log.txt")
        ->pauseBetweenRetriesMs(1000);

    // use it for first request
    echo "Get request: \n";
    $response = $client->url("https://httpbin.org/get")
                       ->get();
    var_dump($response->json());

    // use for second request to other url
    echo "Patch request: \n";
    $response = $client->url("https://httpbin.org/patch")
                       ->patch(["Key" => "value"]);
    var_dump($response->json());

} catch (JttpException $e) {
    // it will catch all errors here including non 2xx response codes and json_decode errors, too many redirects, etc
    echo "Error: {$e->getMessage()}\n";
}