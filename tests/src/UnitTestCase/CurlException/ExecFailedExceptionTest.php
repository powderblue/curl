<?php

declare(strict_types=1);

namespace PowderBlue\Curl\Tests\UnitTestCase\CurlException;

use PowderBlue\Curl\AbstractCurlException;
use PowderBlue\Curl\CurlException\ExecFailedException;
use PowderBlue\Curl\Tests\Helper;
use ztest\UnitTestCase as TestCase;

class ExecFailedExceptionTest extends TestCase
{
    public function test_is_a_curlexception(): void
    {
        Helper::assertSubclassOf(AbstractCurlException::class, ExecFailedException::class);
    }
}
