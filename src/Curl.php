<?php namespace Jttp;

class Curl implements TransportInterface
{
    /**
     * @param string $method "get"|"post"|"put"|...
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
    ): Response
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
        if ($method === "post") {
            curl_setopt($ch, CURLOPT_POST, is_array($data) ? count($data) : 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_VERBOSE, $verbose);
        if ($log_handler) {
            curl_setopt($ch, CURLOPT_STDERR, $log_handler);
        }
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: ' . $body_format,
//                'Content-Length: ' . strlen($data_string))
        ]);

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