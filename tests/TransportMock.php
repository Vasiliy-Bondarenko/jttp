<?php namespace Tests;

use Jttp\Response;
use Jttp\TransportInterface;

class TransportMock implements TransportInterface
{
    protected $response;

    public function call(string $method, string $url, $data = null, bool $verbose = false)
    {
        return $this->response;
    }

    public function returnResponse(Response $response)
    {
        $this->response = $response;
    }
}