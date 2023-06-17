<?php

declare(strict_types=1);

namespace PowderBlue\Curl\Tests;

use function ensure;
use function preg_match;

class Helper
{
    // /**
    //  * @param mixed $value
    //  * @param mixed[] ...$other
    //  */
    // public static function assert_all_equal($value, ...$other): void
    // {
    //     foreach ($other as $argument) {
    //         assert_equal($value, $argument);
    //     }
    // }

    // public static function assert_difference(string $expression, Closure $lambda): void
    // {
    //     $expression = "return {$expression};";
    //     $value = eval($expression);
    //     $lambda();
    //     assert_not_equal($value, eval($expression));
    // }

    // public static function assert_no_difference(string $expression, Closure $lambda): void
    // {
    //     $expression = "return {$expression};";
    //     $value = eval($expression);
    //     $lambda();
    //     assert_equal($value, eval($expression));
    // }

    // /**
    //  * @param mixed $value
    //  */
    // public static function assert_empty($value): void
    // {
    //     ensure(empty($value));
    // }

    /**
     * @param mixed $value
     */
    public static function assertNotEmpty($value): void
    {
        ensure(!empty($value));
    }

    // /**
    //  * @param mixed $needle
    //  * @param mixed[] $haystack
    //  */
    // public static function assert_in_array($needle, array $haystack): void
    // {
    //     ensure(in_array($needle, $haystack));
    // }

    // /**
    //  * @param mixed $needle
    //  * @param mixed[] $haystack
    //  */
    // public static function assert_not_in_array($needle, array $haystack): void
    // {
    //     ensure(!in_array($needle, $haystack));
    // }

    public static function assertMatches(string $pattern, string $subject): void
    {
        ensure(preg_match($pattern, $subject));
    }

    // public static function assert_not_matches(string $pattern, string $subject): void
    // {
    //     ensure(!preg_match($pattern, $subject));
    // }
}
