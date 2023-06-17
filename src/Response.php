<?php

declare(strict_types=1);

namespace PowderBlue\Curl;

use RuntimeException;

use function array_shift;
use function explode;
use function preg_match;

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
     *
     * @throws RuntimeException If the status line is invalid
     * @throws RuntimeException If a header line is invalid
     */
    public function __construct(string $response)
    {
        $responseParts = explode(self::CRLF . self::CRLF, $response);
        // @todo Validate parts

        $headerLines = explode(self::CRLF, $responseParts[0]);

        $statusLine = array_shift($headerLines);
        $statusLineMatches = [];
        $statusLineIsValid = preg_match('~^HTTP/(\d(?:\.\d)?)\s((\d{3})\s(.*))$~', $statusLine, $statusLineMatches);

        if (!$statusLineIsValid) {
            throw new RuntimeException('The status line is invalid');
        }

        list(
            ,
            $this->headers['Http-Version'],
            $this->headers['Status'],
            $this->headers['Status-Code']
        ) = $statusLineMatches;

        foreach ($headerLines as $headerLine) {
            $headerLineMatches = [];
            $headerLineIsValid = preg_match('~^(.*?):\s(.*)~', $headerLine, $headerLineMatches);

            if (!$headerLineIsValid) {
                throw new RuntimeException("The header line `{$headerLine}` is invalid");
            }

            // @todo Normalize name
            $this->headers[$headerLineMatches[1]] = $headerLineMatches[2];
        }

        $this->body = $responseParts[1];
    }

    /**
     * Returns the response body
     *
     * <code>
     * $curl = new PowderBlue\Curl\Curl();
     * $response = $curl->get('https://example.com/');
     * echo $response;  // => echo $response->body;
     * </code>
     */
    public function __toString(): string
    {
        return $this->body;
    }
}
