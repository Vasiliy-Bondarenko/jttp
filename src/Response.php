<?php namespace Jttp;

class Response
{
    protected $statusCode;
    protected $headers;
    protected $body;

    public function __construct($statusCode, $headers, $body)
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->body = $body;
    }

    /**
     * @return int
     */
    public function status()
    {
        return $this->statusCode;
    }

    /**
     * @return array
     */
    public function headers()
    {
        return $this->headers;
    }

    public function body(): string
    {
        return $this->body;
    }

    /**
     * @return mixed
     * @throws JsonException
     */
    public function json()
    {
        return $this->jsonDecodeOrThrow($this->body);
    }

    /**
     * @param $str
     * @return mixed
     * @throws JsonException
     */
    protected function jsonDecodeOrThrow($str)
    {
        if ($str === "") {
            return "";
        }

        $decoded = json_decode($str, true);

        // what do i want if bad json returned?..
        if (json_last_error() !== 0) {
            throw new JsonException("Error: " . json_last_error_msg() . " Json: $str");
        }

        return $decoded;
    }

    /**
     * 2xx and 3xx are Ok.
     * @return bool
     */
    public function isOk(): bool
    {
        return in_array(intdiv($this->statusCode, 100), [2,3]);
    }
}