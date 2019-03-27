<?php namespace Tests;

use Jttp\TransportInterface;
use Jttp\Response;

class CustomTransport implements TransportInterface
{
    /** @var Response */
    protected $response;

    public function call(string $method, string $url, string $body_format, $data = null, bool $verbose = false, $log_handler = null): Response
    {
        return $this->response
            ->setBody("body")
            ->setHeaders("HTTP/1.1 200 OK\r\nHeader: value\r\n\r\n")
            ->setStatusCode(201);
    }

    public function setResponseObject(Response $response)
    {
        $this->response = $response;
    }
}