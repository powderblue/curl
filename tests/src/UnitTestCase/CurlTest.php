<?php

declare(strict_types=1);

namespace PowderBlue\Curl\Tests\UnitTestCase;

use PowderBlue\Curl\Curl;
use PowderBlue\Curl\Response;
use PowderBlue\Curl\Tests\Helper;
use ztest\UnitTestCase as TestCase;

use function assert_identical;
use function assert_match;

use const false;

class CurlTest extends TestCase
{
    public function test_get(): void
    {
        $response = (new Curl())->get('https://example.com/');

        Helper::assertInstanceOf(Response::class, $response);
        /** @var Response $response */
        assert_match('~Example Domain~', $response->body);
        assert_identical('200', $response->headers['Status-Code']);
    }

    public function test_error(): void
    {
        $curl = new Curl();
        $response = $curl->get('diaewkaksdljf-invalid-url-dot-com.com');

        Helper::assertNotEmpty($curl->error());
        assert_identical(false, $response);
    }

    public function test_copes_with_http_2_responses(): void
    {
        $curl = new Curl();
        $response = $curl->get('https://api.zippopotam.us/FR/73550');

        Helper::assertInstanceOf(Response::class, $response);
        /** @var Response $response */
        assert_identical('2', $response->headers['Http-Version']);
        assert_identical('200', $response->headers['Status-Code']);
    }
}
