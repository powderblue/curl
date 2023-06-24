# Curl

A basic cURL wrapper for PHP.

> :information_source: See [https://www.php.net/curl](https://www.php.net/curl) for more information about the cURL extension for PHP

This fork is a modernised, and maintained, version of [@shuber's](https://github.com/shuber) rather nice, simple [cURL wrapper](https://github.com/shuber/curl).

## Installation

Use [Composer](https://getcomposer.org/).

## Usage

### Initialization

Simply require and initialize the `Curl` class like so:

```php
require '<your-project-dir>/vendor/autoload.php';

$curl = new PowderBlue\Curl\Curl();
```

### Performing a Request

The `Curl` class provides shortcuts for making requests using the `HEAD`, `GET`, `POST`, `PUT`, and `DELETE` methods.  You must always specify a URL; you can also pass an array/string of variables to send along with it, if need be.

```php
$response = $curl->head($url, $vars);
$response = $curl->get($url, $vars);  // In this case, the variables will be appended to the URL in the form of a query-string
$response = $curl->post($url, $vars);
$response = $curl->put($url, $vars);
$response = $curl->delete($url, $vars);
```

Use `Curl::request()` to make a request using a custom request-method, thus:

```php
$response = $curl->request('<method-name>', $url, $vars);
```

Examples:

```php
$response = $curl->get('https://www.google.com/?q=test');

// In this case, '?q=test' will be appended to the URL
$response = $curl->get('https://www.google.com/', ['q' => 'test']);

$response = $curl->post('test.com/posts', ['title' => 'Test', 'body' => 'This is a test']);
```

All requests return an instance of `PowderBlue\Curl\Response`&mdash;see below for more information&mdash;or `false` if an error occurred.  You can access the error string with `Curl::error()`.

### The Response Class

A normal cURL request returns the headers and body in a single string.  The `PowderBlue\Curl\Response` class splits that string, placing the two parts in separate properties.

For example:

```php
$response = $curl->get('https://www.google.com/');
echo $response->body;
print_r($response->headers);
```

Would display something like:

```php
<html>
<head>
<title>Google.com</title>
</head>
<body>
...
</body>
</html>

Array
(
    [Http-Version] => 1.0
    [Status-Code] => 200
    [Status] => 200 OK
    [Cache-Control] => private
    [Content-Type] => text/html; charset=ISO-8859-1
    [Date] => Wed, 07 May 2008 21:43:48 GMT
    [Server] => gws
    [Connection] => close
)
```

> :information_source: `PowderBlue\Curl\Response::__toString()` returns the response body, so&mdash;for example&mdash;`echo $response` will output the same as `echo $response->body`.

### Cookies/Sessions

By default, cookies will be stored in `<lib-dir>/var/curl_cookie.txt`.  You can change this by doing something like the following.

```php
$curl->cookie_file = '<pathname>';
```

This allows you to maintain a session across requests.

### Basic Configuration Options

You can easily set the referer or user-agent:

```php
$curl->referer = '<url>';
$curl->user_agent = '<user-agent-string>';
```

### Setting Headers

You can specify headers to send with the request:

```php
$curl->headers['Host'] = 12.345.678.90;
$curl->headers['Custom-Header'] = 'foo';
$curl->headers['User-Agent'] = '<user-agent-string>';
```

### Setting Custom cURL Request Options

By default, redirects will be followed.  You can disable this by setting:

```php
$curl->follow_redirects = false;
```

If you need to do something a little more exotic, you can set/override cURL options like this:

```php
$curl->options[CURLOPT_AUTOREFERER] = true;
```

> :information_source: See the [`curl_setopt()` documentation](https://www.php.net/curl_setopt) for a list of cURL request options

## Get in Touch

Problems, comments, and suggestions are all welcome: [support@powder-blue.com](mailto:support@powder-blue.com).
