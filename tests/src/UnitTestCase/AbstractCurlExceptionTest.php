<?php

declare(strict_types=1);

namespace PowderBlue\Curl\Tests\UnitTestCase;

use PowderBlue\Curl\AbstractCurlException;
use PowderBlue\Curl\Tests\Helper;
use ReflectionClass;
use RuntimeException;
use ztest\UnitTestCase as TestCase;

use function assert_identical;

use const true;

class AbstractCurlExceptionTest extends TestCase
{
    public function test_is_abstract(): void
    {
        $class = new ReflectionClass(AbstractCurlException::class);

        assert_identical(true, $class->isAbstract());
    }

    public function test_is_a_runtimeexception(): void
    {
        Helper::assertSubclassOf(RuntimeException::class, AbstractCurlException::class);
    }
}
