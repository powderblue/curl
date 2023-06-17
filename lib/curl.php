<?php

/**
 * A basic cURL wrapper
 *
 * See the README for documentation/examples or https://www.php.net/curl for more information about the libcurl extension for PHP
 *
 * @author Sean Huber <shuber@huberry.com>
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
     * @phpstan-var HeadersArray
     */
    public array $headers = [];

    /**
     * An associative array of `curl_setopt()` options to send along with requests
     *
     * @var array<array<int,mixed>>
     */
    public array $options = [];

    /**
     * Stores an error string for the last request if one occurred
     */
    protected string $error = '';

    /**
     * Stores resource handle for the current cURL request
     *
     * @var resource
     */
    protected $request;

    /**
     * Sets `$cookie_file` to `<lib-dir>/var/curl_cookie.txt`.
     *
     * Also sets the $user_agent to $_SERVER['HTTP_USER_AGENT'] if it exists, 'Curl/PHP '.PHP_VERSION.' (https://github.com/powderblue/curl)' otherwise
     */
    public function __construct()
    {
        // @todo Do this better
        $this->cookie_file = dirname(__DIR__) . '/var/curl_cookie.txt';

        // @todo Do this better
        $this->user_agent = isset($_SERVER['HTTP_USER_AGENT'])
            ? $_SERVER['HTTP_USER_AGENT']
            : 'Curl/PHP ' . PHP_VERSION . ' (https://github.com/powderblue/curl)'
        ;

        $this->referer = null;
    }

    /**
     * Makes an HTTP request of the specified $method to a $url with an optional array or string of $vars
     *
     * Returns a CurlResponse object if the request was successful, false otherwise
     *
     * @phpstan-param RequestParameters $vars
     * @return CurlResponse|bool
     */
    public function request(string $method, string $url, $vars = [])
    {
        $this->error = '';
        $this->request = curl_init();

        if (is_array($vars)) {
            $vars = http_build_query($vars);
        }

        $this->setRequestMethod($method);
        $this->setRequestOptions($url, $vars);
        $this->setRequestHeaders();

        /** @phpstan-var string|false */
        $response = curl_exec($this->request);

        if (false === $response) {
            $this->error = curl_errno($this->request) . ' - ' . curl_error($this->request);
        } else {
            $response = new CurlResponse($response);
        }

        curl_close($this->request);

        return $response;
    }

    /**
     * Makes an HTTP DELETE request to the specified $url with an optional array or string of $vars
     *
     * Returns a CurlResponse object if the request was successful, false otherwise
     *
     * @phpstan-param RequestParameters $vars
     * @return CurlResponse|bool
     */
    public function delete(string $url, $vars = [])
    {
        return $this->request('DELETE', $url, $vars);
    }

    /**
     * Returns the error string of the current request if one occurred
     */
    public function error(): string
    {
        return $this->error;
    }

    /**
     * Makes an HTTP GET request to the specified $url with an optional array or string of $vars
     *
     * Returns a CurlResponse object if the request was successful, false otherwise
     *
     * @phpstan-param RequestParameters $vars
     * @return CurlResponse|bool
     */
    public function get(string $url, $vars = [])
    {
        if (!empty($vars)) {
            $url .= false === stripos($url, '?')
                ? '?'
                : '&'
            ;

            $url .= is_string($vars)
                ? $vars
                : http_build_query($vars)
            ;
        }

        return $this->request('GET', $url);
    }

    /**
     * Makes an HTTP HEAD request to the specified $url with an optional array or string of $vars
     *
     * Returns a CurlResponse object if the request was successful, false otherwise
     *
     * @phpstan-param RequestParameters $vars
     * @return CurlResponse|bool
     */
    public function head(string $url, $vars = [])
    {
        return $this->request('HEAD', $url, $vars);
    }

    /**
     * Makes an HTTP POST request to the specified $url with an optional array or string of $vars
     *
     * @phpstan-param RequestParameters $vars
     * @return CurlResponse|bool
     */
    public function post(string $url, $vars = [])
    {
        return $this->request('POST', $url, $vars);
    }

    /**
     * Makes an HTTP PUT request to the specified $url with an optional array or string of $vars
     *
     * Returns a CurlResponse object if the request was successful, false otherwise
     *
     * @phpstan-param RequestParameters $vars
     * @return CurlResponse|bool
     */
    public function put(string $url, $vars = [])
    {
        return $this->request('PUT', $url, $vars);
    }

    /**
     * Formats and adds custom headers to the current request
     */
    protected function setRequestHeaders(): void
    {
        $headers = [];

        foreach ($this->headers as $key => $value) {
            $headers[] = "{$key}: {$value}";
        }

        curl_setopt($this->request, CURLOPT_HTTPHEADER, $headers);
    }

    /**
     * Set the associated cURL options for a request method
     */
    protected function setRequestMethod(string $method): void
    {
        switch (strtoupper($method)) {
            case 'HEAD':
                curl_setopt($this->request, CURLOPT_NOBODY, true);
                break;

            case 'GET':
                curl_setopt($this->request, CURLOPT_HTTPGET, true);
                break;

            case 'POST':
                curl_setopt($this->request, CURLOPT_POST, true);
                break;

            default:
                curl_setopt($this->request, CURLOPT_CUSTOMREQUEST, $method);
        }
    }

    /**
     * Sets the CURLOPT options for the current request
     */
    protected function setRequestOptions(string $url, string $vars): void
    {
        curl_setopt($this->request, CURLOPT_URL, $url);

        if (!empty($vars)) {
            curl_setopt($this->request, CURLOPT_POSTFIELDS, $vars);
        }

        // Set some default cURL options
        curl_setopt($this->request, CURLOPT_HEADER, true);
        curl_setopt($this->request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->request, CURLOPT_USERAGENT, $this->user_agent);

        if ($this->cookie_file) {
            curl_setopt($this->request, CURLOPT_COOKIEFILE, $this->cookie_file);
            curl_setopt($this->request, CURLOPT_COOKIEJAR, $this->cookie_file);
        }

        if ($this->follow_redirects) {
            curl_setopt($this->request, CURLOPT_FOLLOWLOCATION, true);
        }

        if (is_string($this->referer)) {
            curl_setopt($this->request, CURLOPT_REFERER, $this->referer);
        }

        $prefix = 'CURLOPT_';

        // Set any custom cURL options
        foreach ($this->options as $option => $value) {
            $curlConstantName = $prefix . str_replace($prefix, '', strtoupper($option));
            // @todo Don't assume: check, and then throw an exception, if necessary
            /** @var int */
            $curlOptionId = constant($curlConstantName);
            curl_setopt($this->request, $curlOptionId, $value);
        }
    }
}
