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
->maxRedirects(5)               // follow maximum 5 redirects
->doNotFollowRedirects()

->asMultipart()                 // send request as multipart/form-data

->logToStderr()                 // enables logging debug information to stderr
->logToFile($filename = "")     // log to file

->retries(1)                    // retry request on network errors, remote server errors, etc 
->pauseBetweenRetriesMs(1000)   // in milliseconds
```
## All [http verbs](https://www.restapitutorial.com/lessons/httpmethods.html)
```
->get()
->post($data = null)
->put($data = null)
->patch($data = null)
->delete($data = null)
```

## Advanced features
You will know when you need them :)
Normally it will happen if need to customize functionality of internal classes or for tests.

### Different transports
Default transport is Curl. But if you want to implement your own transport or extend functionality of this Curl transport - you are free to do it.

#### Extending
```
// extend Curl
class CustomTransport extends Curl
{
    // extend however you want...
}

// and use
$response = (new Jttp)
    ->useTransport((new CustomTransport()))
    ->url("https://httpbin.org/get")
    ->get();
```
#### Fully custom transport
```
class CustomTransport implements TransportInterface
{

    public function setResponseObject(Response $response)
    {
        // TODO: Implement setResponseObject() method.
    }

    /**
     * @param string $method
     * @param string $url
     * @param mixed $data
     * @param bool $verbose
     * @return Response
     * @throws TransportException
     */
    public function call(string $method, string $url, string $body_format, $data = null, bool $verbose = false, $log_handler = null): Response
    {
        // TODO: Implement call() method.
    }
}

// and use
$response = (new Jttp)
    ->useTransport((new CustomTransport()))
    ->url("https://httpbin.org/get")
    ->get();
```

## Custom response objects
```
// extend original class
class CustomResponse extends Response
{
    public function customMethod()
    {
        return "ok";
    }
}

// and inject info Jttp to use
$response = (new Jttp)
    ->useResponseObject(new CustomResponse())
    ->url("https://httpbin.org/get")
    ->get();
    
// use you response
echo $response->customMethod();
```

## Package inspired by
[Zttp by Adam Wathan](https://github.com/kitetail/zttp)