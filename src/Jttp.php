<?php namespace Jttp;

class Jttp
{
    /** @var TransportInterface */
    protected $transport;
    /** @var array */
    protected $urls = [];
    protected $body_format = BodyFormat::JSON;
    protected $redirects = 5;
    /** @var resource */
    protected $log_handler;
    protected $verbose = false;
    protected $retries = 0;
    protected $pauseBetweenRetriesMs = 0;
    /** @var Response */
    protected $response_object;

    public function __construct()
    {
        $this->transport       = new Curl();
        $this->response_object = new Response();
    }

    /**
     * you can inject your own implementation of transport. Curl will be used by default.
     */
    public function useTransport(TransportInterface $transport): Jttp
    {
        $this->transport = $transport;
        return $this;
    }

    public function url(string $url)
    {
        $this->urls[] = $url;
        return $this;
    }

    /**
     * @return Response
     * @throws TransportException
     * @throws HttpException
     * @throws JttpException
     * @throws TooManyRedirectsException
     */
    public function get()
    {
        return $this->call("get", null);
    }

    /**
     * @param $data
     * @return Response
     * @throws TransportException
     * @throws HttpException
     * @throws JttpException
     * @throws TooManyRedirectsException
     */
    public function post($data)
    {
        return $this->call("post", $data);
    }

    /**
     * @param $data
     * @return Response
     * @throws TransportException
     * @throws HttpException
     * @throws JttpException
     * @throws TooManyRedirectsException
     */
    public function patch($data)
    {
        return $this->call("patch", $data);
    }

    /**
     * @param $data
     * @return Response
     * @throws TransportException
     * @throws HttpException
     * @throws JttpException
     * @throws TooManyRedirectsException
     */
    public function delete($data)
    {
        return $this->call("delete", $data);
    }

    /**
     * @param $data
     * @return Response
     * @throws TransportException
     * @throws HttpException
     * @throws JttpException
     * @throws TooManyRedirectsException
     */
    public function put($data)
    {
        return $this->call("put", $data);
    }

    public function asMultipart()
    {
        $this->body_format = BodyFormat::MULTIPART;
        return $this;
    }

    /**
     * Set maximum number of redirects to follow
     * @param int $count
     * @return $this
     */
    public function maxRedirects(int $count)
    {
        $this->redirects = $count;
        return $this;
    }

    /**
     * disable following redirects
     * @return $this
     */
    public function doNotFollowRedirects()
    {
        $this->redirects = 0;
        return $this;
    }

    /**
     * Enables logging debug information to stderr
     * @return $this
     */
    public function logToStderr()
    {
        $this->verbose = true;
        return $this;
    }

    public function logToFile(string $file)
    {
        $this->verbose     = true;
        $this->log_handler = fopen($file, 'w+');
        return $this;
    }

    /**
     * @param string $method
     * @param null $data
     * @return Response
     * @throws TransportException
     * @throws HttpException
     * @throws JttpException
     * @throws TooManyRedirectsException
     */
    protected function call(string $method, $data = null): Response
    {
        if (!isset($this->urls[0])) {
            throw new JttpException("You must call url() method first");
        }

        if ($this->body_format === BodyFormat::JSON) {
            $data = json_encode($data);
        }

        return $this->call_url_with_retries($this->urls[0], $method, $data);
    }

    /**
     * @param string $url
     * @param string $method
     * @param null $data
     * @return Response
     * @throws HttpException
     * @throws JttpException
     * @throws TransportException
     */
    protected function call_url_with_retries(string $url, string $method, $data = null): Response
    {
        try {
            return $this->call_url($url, $method, $data);
        } catch (JttpException $e) {
            // check for non-fatal exceptions like 500 or DNS failure
            if (!($e instanceof HttpException OR $e instanceof TransportException)) {
                throw $e;
            }

            // run out of retries
            if ($this->retries <= 0) {
                throw $e;
            }

            // retry
            $this->retries--;
            usleep($this->pauseBetweenRetriesMs * 1000);
            return $this->call_url_with_retries($url, $method, $data);
        }
    }

    /**
     * @param string $url
     * @param string $method
     * @param null $data
     * @return Response
     * @throws TransportException
     * @throws HttpException
     * @throws TooManyRedirectsException
     */
    protected function call_url(string $url, string $method, $data = null): Response
    {
        $this->transport->setResponseObject($this->response_object);

        $response = $this->transport->call($method, $url, $this->body_format, $data, $this->verbose, $this->log_handler);
        
        if (StatusCodes::isSafeToAutoRedirect($response->status())) {
            if ($this->redirects == 0) {
                throw new TooManyRedirectsException($response, "Too many redirects.");
            }
            $this->redirects--;
            return $this->call_url($response->header("Location"), $method, $data);
        }

        if (!StatusCodes::isOk($response->status())) {
            $redir = $this->redirects > 0 ? " or 3xx redirect" : "";
            throw new HttpException($response, "Expected result with 2xx success status code{$redir}. Returned: {$response->status()}");
        }

        return $response;
    }

    public function useResponseObject(Response $response_object): Jttp
    {
        $this->response_object = $response_object;
        return $this;
    }

    public function retries(int $times): Jttp
    {
        $this->retries = $times;
        return $this;
    }

    public function pauseBetweenRetriesMs(int $ms): Jttp
    {
        $this->pauseBetweenRetriesMs = $ms;
        return $this;
    }
}