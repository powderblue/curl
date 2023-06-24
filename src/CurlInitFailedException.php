<?php

declare(strict_types=1);

namespace PowderBlue\Curl;

use RuntimeException;

class CurlInitFailedException extends RuntimeException
{
    /** @phpstan-ignore-next-line */
    protected $message = 'The `curl_init()` call failed';
}
