<?php namespace Jttp;

use Throwable;

class TooManyRedirectsException extends JttpException
{
    /**
     * @var Response
     */
    public $response;

    public function __construct(Response $response, string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->response = $response;
    }
}