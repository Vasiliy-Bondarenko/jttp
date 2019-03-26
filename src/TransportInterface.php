<?php namespace Jttp;

interface TransportInterface
{
    /**
     * @param string $method
     * @param string $url
     * @param mixed $data
     * @param bool $verbose
     * @return Response
     * @throws CurlException
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