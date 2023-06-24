<?php

declare(strict_types=1);

namespace PowderBlue\Curl\Tests\UnitTestCase;

use PowderBlue\Curl\CurlInitFailedException;
use PowderBlue\Curl\Tests\Helper;
use RuntimeException;
use ztest\UnitTestCase as TestCase;

class CurlInitFailedExceptionTest extends TestCase
{
    public function test_is_a_runtimeexception(): void
    {
        Helper::assertSubclassOf(RuntimeException::class, CurlInitFailedException::class);
    }
}
