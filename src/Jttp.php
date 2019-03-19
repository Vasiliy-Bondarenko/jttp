<?php namespace Jttp;

class Jttp
{
    /** @var TransportInterface */
    protected $transport;
    /** @var array */
    protected $urls = [];
    protected $body_format = BodyFormat::JSON;

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
     * @param string $method
     * @param string $endpoint
     * @param mixed $data
     * @return Response
     * @throws JttpException
     */
    protected function call(string $method, $data = null, bool $verbose = false)
    {
        if (!isset($this->urls[0])) {
            throw new JttpException("You must call url() method first");
        }

        if ($this->body_format === BodyFormat::JSON) {
            $data = json_encode($data);
        }

        return $this->transport->call($method, $this->urls[0], $this->body_format, $data, $verbose);
    }
}