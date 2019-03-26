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

    /**
     * @param TransportInterface $transport - you can inject your own implementation of transport. Curl will be used by default.
     */
    public function __construct(TransportInterface $transport = null)
    {
        $this->transport = is_null($transport) ? new Curl() : $transport;
    }

    public function url(string $url)
    {
        $this->urls[] = $url;
        return $this;
    }

    public function get()
    {
        return $this->call("get", null);
    }

    public function post($data)
    {
        return $this->call("post", $data);
    }

    public function patch($data)
    {
        return $this->call("patch", $data);
    }

    public function delete($data)
    {
        return $this->call("delete", $data);
    }

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
        $this->verbose = true;
        $this->log_handler = fopen($file, 'w+');
        return $this;
    }

    /**
     * @param string $method
     * @param string $endpoint
     * @param mixed $data
     * @return Response
     * @throws JttpException
     */
    protected function call(string $method, $data = null): Response
    {
        if (!isset($this->urls[0])) {
            throw new JttpException("You must call url() method first");
        }

        if ($this->body_format === BodyFormat::JSON) {
            $data = json_encode($data);
        }

        return $this->call_url($this->urls[0], $method, $data);
    }

    protected function call_url(string $url, string $method, $data = null): Response
    {
        $response = $this->transport->call($method, $url, $this->body_format, $data, $this->verbose, $this->log_handler);

        if ($response->status() === 302) { // fixme
            if ($this->redirects == 0) {
                throw new TooManyRedirectsException($response, "Too many redirects.");
            }
            $this->redirects--;
            return $this->call_url($response->header("Location"), $method, $data);
        }

        if (intdiv($response->status(), 100) !== 2) {
            throw new HttpException($response, "Expected results with 2xx status code. Returned: {$response->status()}");
        }

        return $response;
    }
}