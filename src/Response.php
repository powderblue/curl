<?php

declare(strict_types=1);

namespace PowderBlue\Curl;

use RuntimeException;

use function array_shift;
use function explode;
use function json_decode;
use function json_last_error;
use function preg_match;
use function strtolower;

use const JSON_ERROR_NONE;
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
     * @phpstan-var Headers
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
     * @throws RuntimeException If the status line is invalid
     * @throws RuntimeException If a header line is invalid
     */
    public function __construct(string $response)
    {
        // @todo Validate resulting parts.  Remember: it's okay for the body to be empty.
        $responseParts = explode(self::CRLF . self::CRLF, $response);

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
     * Like [`Response.json()` in JavaScript](https://developer.mozilla.org/en-US/docs/Web/API/Response/json): "Note
     * that despite the method being named json(), the result is not JSON but is instead the result of taking JSON as
     * input and parsing it to produce [an] object."
     *
     * @return mixed
     * @throws RuntimeException If the content is not JSON
     * @throws RuntimeException If the JSON is invalid
     */
    public function json(?bool $associative = null)
    {
        if ('application/json' !== $this->contentType) {
            throw new RuntimeException('The content is not JSON');
        }

        $decoded = json_decode($this->body, $associative);

        $jsonErrorId = json_last_error();

        if (JSON_ERROR_NONE !== $jsonErrorId) {
            throw new RuntimeException('The JSON is invalid', $jsonErrorId);
        }

        return $decoded;
    }

    /**
     * Returns the response body
     */
    public function __toString(): string
    {
        return $this->body;
    }
}
