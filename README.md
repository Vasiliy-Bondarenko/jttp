# Simple HTTP client

## Status
Work in progress!

## Motivation
Create simple to use http client with minimum dependencies.

- Laravel style
- Nice fluid interface
- JSON by default
- Code completion
- No magic
- Strict types where possible 
- PHP 7.0
- Assertions included in response for easy use in tests.
- Documentation embedded in docblocks. You do not need to read documentation in advance.
- Fully tested
- Easily extendable
- Follow redirects by default
- Throw exception on any error (non-2xx status, too many redirects, json_decode error, etc)

## Simple example

```php
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
```
## Other fluid methods
```
->maxRedirects(5) // follow maximum 5 redirects
->doNotFollowRedirects()
->asMultipart() // send request as multipart/form-data
->logToStderr() // enables logging debug information to stderr
->logToFile($filename = "") // log to file
```
## All [http verbs](https://www.restapitutorial.com/lessons/httpmethods.html)
```
->get()
->post($data = null)
->put($data = null)
->patch($data = null)
->delete($data = null)
```


## Package inspired by
[Zttp by Adam Wathan](https://github.com/kitetail/zttp)