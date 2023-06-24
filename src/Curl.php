<?php

declare(strict_types=1);

namespace PowderBlue\Curl;

use function array_replace;
use function curl_close;
use function curl_errno;
use function curl_error;
use function curl_exec;
use function curl_init;
use function curl_setopt_array;
use function dirname;
use function http_build_query;
use function strpos;
use function strtoupper;

use const CURLOPT_COOKIEFILE;
use const CURLOPT_COOKIEJAR;
use const CURLOPT_CUSTOMREQUEST;
use const CURLOPT_FOLLOWLOCATION;
use const CURLOPT_HEADER;
use const CURLOPT_HTTPHEADER;
use const CURLOPT_NOBODY;
use const CURLOPT_POST;
use const CURLOPT_POSTFIELDS;
use const CURLOPT_REFERER;
use const CURLOPT_RETURNTRANSFER;
use const CURLOPT_URL;
use const CURLOPT_USERAGENT;
use const false;
use const null;
use const PHP_VERSION;
use const true;

/**
 * A basic cURL wrapper
 *
 * See the README for documentation/examples or https://www.php.net/curl for more information about the libcurl extension for PHP
 *
 * @todo Throw exceptions
 * @todo Check/update types/values
 */
class Curl
{
    /**
     * The file to read and write cookies to for requests
     */
    public string $cookie_file;

    /**
     * The user agent to send along with requests
     */
    public string $user_agent;

    /**
     * The value of the referer header to send in all requests
     */
    public ?string $referer;

    /**
     * Determines whether or not requests should follow redirects
     */
    public bool $follow_redirects = true;

    /**
     * An associative array of headers to send along with requests
     *
     * @phpstan-var Headers
     */
    public array $headers = [];

    /**
     * An associative array of `curl_setopt()` options to send along with requests
     *
     * @phpstan-var CurlOptions
     */
    public array $options = [];

    /**
     * The description of the cURL error that just occurred, if applicable
     */
    private string $error = '';

    /**
     * Sets:
     * - `$cookie_file` to `<lib-dir>/var/curl_cookie.txt`;
     * - `$user_agent` to something appropriate.
     */
    public function __construct()
    {
        // @todo Just use the system's temp dir
        $this->cookie_file = dirname(__DIR__) . '/var/curl_cookie.txt';

        // @todo Do this better
        $this->user_agent = isset($_SERVER['HTTP_USER_AGENT'])
            ? $_SERVER['HTTP_USER_AGENT']
            : 'Curl/PHP ' . PHP_VERSION . ' (https://github.com/powderblue/curl)'
        ;

        $this->referer = null;
    }

    /**
     * @return string[]
     */
    private function createHeaderLines(): array
    {
        $headers = [];

        foreach ($this->headers as $key => $value) {
            $headers[] = "{$key}: {$value}";
        }

        return $headers;
    }

    /**
     * Creates the list of cURL options to apply in the current transfer
     *
     * @phpstan-param CurlPostFields $requestBody
     * @phpstan-return CurlOptions
     */
    private function createCurlOptions(
        string $method,
        string $url,
        $requestBody
    ): array {
        // Normalize
        $method = strtoupper($method);

        $curlOptions = [
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_URL => $url,

            CURLOPT_COOKIEFILE => $this->cookie_file,
            CURLOPT_COOKIEJAR => $this->cookie_file,
            CURLOPT_USERAGENT => $this->user_agent,
            CURLOPT_REFERER => $this->referer,
            CURLOPT_FOLLOWLOCATION => $this->follow_redirects,
        ];

        // Add method-specific options
        switch ($method) {
            case 'HEAD':
                $curlOptions[CURLOPT_NOBODY] = true;
                break;

            case 'POST':
                $curlOptions[CURLOPT_POST] = true;
                break;
        }

        if (null !== $requestBody) {
            $curlOptions[CURLOPT_POSTFIELDS] = $requestBody;
        }

        // (In theory) adding the headers now essentially gives the user a chance to override defaults
        $curlOptions[CURLOPT_HTTPHEADER] = $this->createHeaderLines();

        // Merging user options now means defaults can be overridden
        $curlOptions = array_replace($curlOptions, $this->options);

        return $curlOptions;
    }

    private function setError(string $error): self
    {
        $this->error = $error;

        return $this;
    }

    /**
     * Returns the description of the cURL error that just occurred, if applicable
     */
    public function error(): string
    {
        return $this->error;
    }

    /**
     * @phpstan-param CurlPostFields $requestBody
     * @return Response|bool
     */
    public function request(
        string $method,
        string $url,
        $requestBody = null
    ) {
        $this->setError('');
        // @todo Handle error
        $curlHandle = curl_init();

        try {
            $curlOptions = $this->createCurlOptions($method, $url, $requestBody);
            // @todo Handle error
            curl_setopt_array($curlHandle, $curlOptions);

            /** @phpstan-var string|false */
            $response = curl_exec($curlHandle);

            if (false === $response) {
                $this->setError(curl_errno($curlHandle) . ' - ' . curl_error($curlHandle));
            } else {
                $response = new Response($response);
            }
        } finally {
            curl_close($curlHandle);
        }

        return $response;
    }

    /**
     * See https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/DELETE
     *
     * @phpstan-param CurlPostFields $requestBody
     * @return Response|bool
     */
    public function delete(string $url, $requestBody = null)
    {
        return $this->request('DELETE', $url, $requestBody);
    }

    /**
     * Also see https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/HEAD
     *
     * @phpstan-param RequestParameters $requestParams
     * @return Response|bool
     */
    public function get(
        string $url,
        array $requestParams = [],
        bool $noBody = false
    ) {
        $method = $noBody ? 'HEAD' : 'GET';

        if ($requestParams) {
            $separator = false === strpos($url, '?') ? '?' : '&';
            $url .= $separator . http_build_query($requestParams);
        }

        return $this->request($method, $url);
    }

    /**
     * See https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/HEAD
     *
     * @phpstan-param RequestParameters $requestParams
     * @return Response|bool
     */
    public function head(string $url, array $requestParams = [])
    {
        return $this->get($url, $requestParams, true);
    }

    /**
     * @phpstan-param CurlPostFields $requestBody
     * @return Response|bool
     */
    public function post(string $url, $requestBody = null)
    {
        return $this->request('POST', $url, $requestBody);
    }

    /**
     * See https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/PUT
     *
     * @phpstan-param CurlPostFields $requestBody
     * @return Response|bool
     */
    public function put(string $url, $requestBody = null)
    {
        return $this->request('PUT', $url, $requestBody);
    }
}
