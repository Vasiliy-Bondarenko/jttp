<?php namespace Jttp;

interface TransportInterface
{
    public function setResponseObject(Response $response);

    /**
     * @param string $method
     * @param string $url
     * @param mixed $data
     * @param bool $verbose
     * @return Response
     * @throws TransportException
     */
    public function call(
        string $method,
        string $url,
        string $body_format,
        $data = null,
        bool $verbose = false,
        $log_handler = null
    ): Response;
}