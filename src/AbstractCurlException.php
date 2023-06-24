<?php

declare(strict_types=1);

namespace PowderBlue\Curl;

use RuntimeException;
use Throwable;

use function curl_errno;
use function curl_error;

use const null;

abstract class AbstractCurlException extends RuntimeException
{
    /**
     * @param resource $curlHandle
     * @param Throwable|null $previous
     */
    public function __construct($curlHandle, ?Throwable $previous = null)
    {
        parent::__construct(
            curl_error($curlHandle),
            curl_errno($curlHandle),
            $previous
        );
    }
}
