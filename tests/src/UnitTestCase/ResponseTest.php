<?php

declare(strict_types=1);

namespace PowderBlue\Curl\Tests\UnitTestCase;

use PowderBlue\Curl\Curl;
use PowderBlue\Curl\Response;
use PowderBlue\Curl\Tests\Helper;
use ztest\UnitTestCase as TestCase;

use function assert_array;
use function assert_identical;
use function ob_get_clean;
use function ob_start;

use const false;
use const true;

class ResponseTest extends TestCase
{
    public function test_should_set_the_value_of_ok(): void
    {
        $ok = new Response("HTTP/2 200 \r\n\r\n");

        assert_identical(true, $ok->ok);
        assert_identical(200, $ok->status);

        $ok = new Response("HTTP/2 299 Better Than OK\r\n\r\n");

        assert_identical(true, $ok->ok);
        assert_identical(299, $ok->status);

        $continue = new Response("HTTP/2 100 Continue\r\n\r\n");

        assert_identical(false, $continue->ok);
        assert_identical(100, $continue->status);

        $seeOther = new Response("HTTP/2 300 Multiple Choices\r\n\r\n");

        assert_identical(false, $seeOther->ok);
        assert_identical(300, $seeOther->status);

        $notFound = new Response("HTTP/2 404 Not Found\r\n\r\n");

        assert_identical(false, $notFound->ok);
        assert_identical(404, $notFound->status);

        $internalServerError = new Response("HTTP/2 500 Internal Server Error\r\n\r\n");

        assert_identical(false, $internalServerError->ok);
        assert_identical(500, $internalServerError->status);
    }

    public function test_should_separate_response_headers_from_the_body(): void
    {
        $response = (new Curl())->get('https://example.com/');

        Helper::assertInstanceOf(Response::class, $response);
        /** @var Response $response */
        assert_array($response->headers);
        Helper::assertNotEmpty($response->headers);
        Helper::assertMatches('~^<!doctype~', $response->body);
    }

    public function test_should_set_status_headers(): void
    {
        $response = (new Curl())->get('https://example.com/');

        Helper::assertInstanceOf(Response::class, $response);
        /** @var Response $response */
        assert_identical('200', $response->headers['Status-Code']);
        assert_identical('200 ', $response->headers['Status']);
        assert_identical('2', $response->headers['Http-Version']);
    }

    public function test_tostring_should_return_the_response_body(): void
    {
        $response = (new Curl())->get('https://example.com/');

        Helper::assertInstanceOf(Response::class, $response);
        /** @var Response $response */

        ob_start();
        echo $response;
        $output = ob_get_clean();

        assert_identical($response->body, $output);
    }
}
