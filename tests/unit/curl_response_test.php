<?php

use PowderBlue\Curl\Tests\Helper;
use ztest\UnitTestCase as TestCase;

class CurlResponseTest extends TestCase
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

    public function test_should_separate_response_headers_from_body(): void
    {
        ensure(is_array($this->response->headers));

        Helper::assertMatches('~^<!doctype~', $this->response->body);
    }

    public function test_should_set_status_headers(): void
    {
        assert_equal(200, $this->response->headers['Status-Code']);
        assert_equal('200 OK', $this->response->headers['Status']);
    }

    public function test_should_return_response_body_when_calling_toString(): void
    {
        ob_start();
        echo $this->response;
        assert_equal($this->response->body, ob_get_clean());
    }
}
