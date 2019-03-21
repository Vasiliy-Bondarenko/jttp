<?php namespace Jttp;

class Jttp
{
    /** @var TransportInterface */
    protected $transport;
    /** @var array */
    protected $urls = [];
    protected $body_format = BodyFormat::JSON;
    protected $redirects = 5;

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

    public function get(bool $verbose = false)
    {
        return $this->call("get", null, $verbose);
    }

    public function post($data, bool $verbose = false)
    {
        return $this->call("post", $data, $verbose);
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
     * @param string $method
     * @param string $endpoint
     * @param mixed $data
     * @return Response
     * @throws JttpException
     */
    protected function call(string $method, $data = null, bool $verbose = false): Response
    {
        if (!isset($this->urls[0])) {
            throw new JttpException("You must call url() method first");
        }

        if ($this->body_format === BodyFormat::JSON) {
            $data = json_encode($data);
        }

        return $this->call_url($this->urls[0], $method, $data, $verbose);
    }

    protected function call_url(string $url, string $method, $data = null, bool $verbose = false): Response
    {
        $response = $this->transport->call($method, $url, $this->body_format, $data, $verbose);

        if ($response->status() === 302) { // fixme
            if ($this->redirects == 0) {
                throw new TooManyRedirectsException($response, "Too many redirects.");
            }
            $this->redirects--;
            return $this->call_url($response->header("Location"), $method, $data, $verbose);
        }

        return $response;
    }
}