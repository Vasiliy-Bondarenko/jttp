<?php namespace Tests;

use Jttp\TransportException;
use Jttp\TransportInterface;
use Jttp\Response;

class TransportMockFailingDNS implements TransportInterface
{
    /** @var Response */
    protected $response;
    protected $times = 0;

    public function setResponse(Response $response): TransportMockFailingDNS
    {
        $this->response = $response;
        return $this;
    }

    public function failDnsTimes(int $times): TransportMockFailingDNS
    {
        $this->times = $times;
        return $this;
    }

    public function call(string $method, string $url, string $body_format, $data = null, bool $verbose = false, $log_handler = null): Response
    {
        if ($this->times > 0) {
            $this->times--;
            throw new TransportException("Could not resolve host ...");
        }

        return $this->response;
    }

    public function setResponseObject(Response $response)
    {
        // not used
    }
}