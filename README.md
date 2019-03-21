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

## Examples

```php
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
} catch (\VABondarenko\JsonException $e) {
    echo "Json decode error: " . $e->getMessage() "\n";
    return;
}
```

```
maxRedirects(5) // follow maximum 5 redirects
doNotFollowRedirects()

```


## Package inspired by
[Zttp by Adam Wathan](https://github.com/kitetail/zttp)