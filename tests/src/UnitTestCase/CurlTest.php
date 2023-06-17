<?php

declare(strict_types=1);

namespace PowderBlue\Curl\Tests\UnitTestCase;

use PowderBlue\Curl\Curl;
use PowderBlue\Curl\Response;
use PowderBlue\Curl\Tests\Helper;
use ztest\UnitTestCase as TestCase;

use function assert_equal;

class CurlTest extends TestCase
{
    private Curl $curl;

    private Response $response;

    public function setup(): void
    {
        $this->curl = new Curl();

        /** @phpstan-var Response */
        $response = $this->curl->get('www.google.com');
        $this->response = $response;
    }

    public function test_get(): void
    {
        Helper::assertMatches('~google~', $this->response->body);
        assert_equal(200, $this->response->headers['Status-Code']);
    }

    public function test_error(): void
    {
        $this->curl->get('diaewkaksdljf-invalid-url-dot-com.com');

        Helper::assertNotEmpty($this->curl->error());
    }
}
