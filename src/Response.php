<?php namespace Jttp;

class Response
{
    /** @var int */
    protected $statusCode;
    /** @var array */
    protected $headers = [];
    /** @var string */
    protected $body;

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
     * only 2xx status is Ok.
     * @param array $code_classes = for 2xx and 3xx codes to be ok pass [2,3]
     * @return bool
     */
    public function isOk($code_classes = [2]): bool
    {
        return in_array(intdiv($this->statusCode, 100), $code_classes);
    }

    /**
     * @param string $header
     * @return string|null
     */
    public function header(string $header)
    {
        return $this->headers[$header] ?? null;
    }

    protected function parseHeaders(string $headers)
    {
        $headers = trim($headers); // trim empty line in the end
        $rows = explode("\r\n", $headers); // split into rows
        
        $headers = [];
        foreach ($rows as $index => $header) {
            if ($index === 0) continue; // skip status code on the first line
            list($name, $value) = explode(": ", $header);
            $headers[$name] = $value;
        }

        return $headers;
    }

    public function statusText()
    {
        return StatusCodes::toText($this->status());
    }

    /**
     * @param string $body
     */
    public function setBody(string $body)
    {
        $this->body = $body;
        return $this;
    }

    public function setHeaders(string $headers)
    {
        $this->headers = $this->parseHeaders($headers);
        return $this;
    }

    /**
     * @param int $statusCode
     */
    public function setStatusCode(int $statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }
}