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

class ResponseTest extends TestCase
{
    public function test_should_separate_response_headers_from_the_body(): void
    {
        $response = (new Curl())->get('https://example.com/');

        Helper::assertInstanceOf(Response::class, $response);
        /** @var Response $response */
        assert_array($response->headers);
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
