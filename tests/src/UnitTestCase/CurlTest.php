<?php

declare(strict_types=1);

namespace PowderBlue\Curl\Tests\UnitTestCase;

use PowderBlue\Curl\Curl;
use PowderBlue\Curl\CurlException\ExecFailedException;
use PowderBlue\Curl\CurlException\SetOptFailedException;
use PowderBlue\Curl\Response;
use PowderBlue\Curl\Tests\Helper;
use ztest\UnitTestCase as TestCase;

use function assert_identical;
use function assert_match;
use function assert_throws;

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

    public function test_request_should_throw_an_exception_if_it_fails_to_apply_options_to_the_curl_session(): void
    {
        $curl = new Curl();
        /** @phpstan-ignore-next-line */
        $curl->options['foo'] = 'Invalid option';

        assert_throws(SetOptFailedException::class, function () use ($curl): void {
            $curl->get('https://example.com/');
        });
    }

    public function test_request_should_throw_an_exception_if_it_fails_to_execute_the_curl_session(): void
    {
        $curl = new Curl();

        assert_throws(ExecFailedException::class, function () use ($curl): void {
            $curl->get('diaewkaksdljf-invalid-url-dot-com.com');
        });
    }

    public function test_request_should_cope_with_http_2_responses(): void
    {
        $curl = new Curl();
        $response = $curl->get('https://api.zippopotam.us/FR/73550');

        Helper::assertInstanceOf(Response::class, $response);
        /** @var Response $response */
        assert_identical('2', $response->headers['Http-Version']);
        assert_identical('200', $response->headers['Status-Code']);
    }
}
