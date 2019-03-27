<?php namespace Jttp;

class Jttp
{
    /** @var TransportInterface */
    protected $transport;
    /** @var array */
    protected $urls = [];
    protected $body_format = BodyFormat::JSON;
    /** @var resource */
    protected $log_handler;
    protected $verbose = false;
    protected $pauseBetweenRetriesMs = 0;
    /** @var Response */
    protected $response_object;

    protected $redirects = 5;
    protected $redirects_counter = 0;

    protected $retries = 0;
    protected $retries_counter = 0;

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
        $this->urls[0] = $url;
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
    public function redirects(int $count)
    {
        $this->redirects = $count;
        $this->redirects_counter = $count;
        return $this;
    }

    /**
     * disable following redirects
     * @return $this
     */
    public function doNotFollowRedirects()
    {
        $this->redirects_counter = 0;
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

        $response = $this->callUrlWithRetries($this->urls[0], $method, $data);
        $this->resetCounters();
        return $response;
    }

    protected function resetCounters()
    {
        $this->retries_counter = $this->retries;
        $this->redirects_counter = $this->redirects;
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
    protected function callUrlWithRetries(string $url, string $method, $data = null): Response
    {
        try {
            return $this->call_url($url, $method, $data);
        } catch (JttpException $e) {
            // check for non-fatal exceptions like 500 or DNS failure
            if (!($e instanceof HttpException OR $e instanceof TransportException)) {
                throw $e;
            }

            // run out of retries
            if ($this->retries_counter <= 0) {
                throw $e;
            }

            // retry
            $this->retries_counter--;
            usleep($this->pauseBetweenRetriesMs * 1000);
            return $this->callUrlWithRetries($url, $method, $data);
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
            var_dump($this->redirects_counter);
            var_dump("Going to " . $response->header("Location"));
            if ($this->redirects_counter == 0) {
                throw new TooManyRedirectsException($response, "Too many redirects.");
            }
            $this->redirects_counter--;
            return $this->call_url($response->header("Location"), $method, $data);
        }

        if (!StatusCodes::isOk($response->status())) {
            $redir = $this->redirects_counter > 0 ? " or 3xx redirect" : "";
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
        $this->retries_counter = $times;
        return $this;
    }

    public function pauseBetweenRetriesMs(int $ms): Jttp
    {
        $this->pauseBetweenRetriesMs = $ms;
        return $this;
    }
}