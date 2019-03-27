<?php namespace Tests;

use Jttp\TransportInterface;
use Jttp\Response;

class TransportMock implements TransportInterface
{
    /** @var []Response */
    protected $mocked_responses = [];
    protected $mocked_response_index = 0;

    /**
     * @param []Response $responses
     * @return $this
     */
    public function setResponses(array $responses)
    {
        $this->mocked_responses = $responses;
        return $this;
    }

    public function call(string $method, string $url, string $body_format, $data = null, bool $verbose = false, $log_handler = null): Response
    {
        $response = $this->mocked_responses[$this->mocked_response_index];
        $this->mocked_response_index++;
        return $response;
    }

    public function setResponseObject(Response $response)
    {
        $this->response = $response;
    }
}