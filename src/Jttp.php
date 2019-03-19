<?php namespace Jttp;

class Jttp
{
    /** @var TransportInterface */
    protected $transport;
    /** @var array */
    protected $urls = [];

    /**
     * @param TransportInterface $transport - you can inject your own implementation of transport. Curl will be used by default.
     */
    public function __construct(TransportInterface $transport = null)
    {
        $this->transport      = is_null($transport) ? new Curl() : $transport;
    }

    public function url(string $url)
    {
        $this->urls[] = $url;
        return $this;
    }

    public function get(bool $verbose = false)
    {
        return $this->call("get", $this->urls[0], null, $verbose);
    }

    /**
     * @param string $method
     * @param string $endpoint
     * @param mixed $data
     * @return Response
     * @throws JttpException
     */
    protected function call(string $method, string $endpoint, $data = null, bool $verbose = false)
    {
        return $this->transport->call($method, $endpoint, $data, $verbose);
    }
}