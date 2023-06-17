<?php

use PowderBlue\Curl\Tests\Helper;
use ztest\UnitTestCase as TestCase;

class CurlTest extends TestCase
{
    private Curl $curl;

    private CurlResponse $response;

    public function setup(): void
    {
        $this->curl = new Curl();

        /** @phpstan-var CurlResponse */
        $response = $this->curl->get('www.google.com');
        $this->response = $response;
    }

    public function test_get(): void
    {
        Helper::assertMatches('~google~', $this->response);
        assert_equal(200, $this->response->headers['Status-Code']);
    }

    public function test_error(): void
    {
        $this->curl->get('diaewkaksdljf-invalid-url-dot-com.com');

        Helper::assertNotEmpty($this->curl->error());
    }
}
