<?php

declare(strict_types=1);

namespace PowderBlue\Curl;

use RuntimeException;

use function array_shift;
use function explode;
use function json_decode;
use function preg_match;
use function strtolower;

use const null;

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
     * `true` if the request was successful (status in the range 200-299), or `false` otherwise
     *
     * @readonly
     */
    public bool $ok;

    /**
     * HTTP status code of the response
     *
     * @readonly
     */
    public int $status;

    private ?string $contentType;

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

        $this->status = (int) $this->headers['Status-Code'];
        $this->ok = $this->status >= 200 && $this->status <= 299;

        foreach ($headerLines as $headerLine) {
            $headerLineMatches = [];
            $headerLineIsValid = preg_match('~^(.*?):\s(.*)~', $headerLine, $headerLineMatches);

            if (!$headerLineIsValid) {
                throw new RuntimeException("The header line `{$headerLine}` is invalid");
            }

            $normalizedHeaderName = ucwords(strtolower($headerLineMatches[1]), '-');
            $this->headers[$normalizedHeaderName] = $headerLineMatches[2];
        }

        $this->contentType = null;

        if (array_key_exists('Content-Type', $this->headers)) {
            $matches = [];
            $matched = preg_match('~^(.*?)(?:;|$)~', $this->headers['Content-Type'], $matches);

            if ($matched) {
                $this->contentType = $matches[1];
            }
        }

        $this->body = $responseParts[1];
    }

    /**
     * @return mixed
     * @throws RuntimeException If the content is not JSON
     */
    public function json()
    {
        if ('application/json' !== $this->contentType) {
            throw new RuntimeException('The content is not JSON');
        }

        return json_decode($this->body);
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
