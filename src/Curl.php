<?php namespace Jttp;

class Curl implements TransportInterface
{
    /**
     * @param $url
     * @param mixed $data
     * @param bool $verbose
     * @return Response
     * @throws CurlException
     */
    public function call(string $method, string $url, $data = null, bool $verbose = false)
    {
        $ch = curl_init();
        if (!$ch) {
            throw new CurlException("Can't init curl session");
        }

        // prepare a call
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
//        curl_setopt($ch, CURLOPT_POST, count($data));
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_VERBOSE, $verbose);
        curl_setopt($ch, CURLOPT_HEADER, true);

        // execute call to API
        $response = curl_exec($ch);

        // extract status, headers and body
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers     = substr($response, 0, $header_size);
        $httpcode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $body        = substr($response, $header_size);

        // close resource handler
        curl_close($ch);

        return new Response($httpcode,$headers, $body);
    }
}