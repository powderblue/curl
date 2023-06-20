<?php

declare(strict_types=1);

namespace PowderBlue\Curl\Tests;

use function ensure;

class Helper
{
    /**
     * @param mixed $value
     */
    public static function assertNotEmpty($value): void
    {
        ensure(!empty($value));
    }

    /**
     * @param mixed $actual
     */
    public static function assertInstanceOf(
        string $expectedClassName,
        $actual,
        string $message = ''
    ): void {
        ensure($actual instanceof $expectedClassName, $message);
    }
}
