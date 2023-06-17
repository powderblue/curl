<?php

declare(strict_types=1);

namespace PowderBlue\Curl\Tests\UnitTestCase;

use PowderBlue\Curl\Curl;
use PowderBlue\Curl\Response;
use PowderBlue\Curl\Tests\Helper;
use ztest\UnitTestCase as TestCase;

use function assert_equal;
use function ensure;
use function is_array;
use function ob_get_clean;
use function ob_start;

class ResponseTest extends TestCase
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
