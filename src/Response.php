<?php

declare(strict_types=1);

namespace PowderBlue\Curl;

use function array_pop;
use function array_shift;
use function explode;
use function preg_match;
use function preg_match_all;
use function str_replace;

/**
 * Parses the response from a cURL request into an object containing the response body and an associative array of headers
 */
class Response
{
    /** @var string */
    private const CRLF = "\r\n";

    /**
     * The body of the response without the headers block
     */
    public string $body = '';

    /**
     * An associative array containing the response's headers
     *
     * @phpstan-var HeadersArray
     */
    public array $headers = [];

    /**
     * Accepts the result of a cURL request as a string
     *
     * <code>
     * $response = new PowderBlue\Curl\Response(curl_exec($curl_handle));
     * echo $response->body;
     * echo $response->headers['Status'];
     * </code>
     */
    public function __construct(string $response)
    {
        // Headers regex
        $pattern = '~HTTP/\d\.\d.*?$.*?\r\n\r\n~ims';

        // Extract headers from response
        preg_match_all($pattern, $response, $matches);
        $headers_string = array_pop($matches[0]);
        $headers = explode(self::CRLF, str_replace(self::CRLF . self::CRLF, '', $headers_string));

        // Remove headers from the response body
        $this->body = str_replace($headers_string, '', $response);

        // Extract the version and status from the first header
        $version_and_status = array_shift($headers);
        preg_match('~HTTP/(\d\.\d)\s(\d\d\d)\s(.*)~', $version_and_status, $matches);
        $this->headers['Http-Version'] = $matches[1];
        $this->headers['Status-Code'] = $matches[2];
        $this->headers['Status'] = $matches[2] . ' ' . $matches[3];

        // Convert headers into an associative array
        foreach ($headers as $header) {
            preg_match('~(.*?)\:\s(.*)~', $header, $matches);
            $this->headers[$matches[1]] = $matches[2];
        }
    }

    /**
     * Returns the response body
     *
     * <code>
     * $curl = new Curl();
     * $response = $curl->get('google.com');
     * echo $response;  // => echo $response->body;
     * </code>
     */
    public function __toString(): string
    {
        return $this->body;
    }
}
