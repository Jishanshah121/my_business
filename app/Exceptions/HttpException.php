<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * An exception that maps directly to an HTTP status. The message is safe to
 * show a customer — never put internal detail in it.
 */
class HttpException extends RuntimeException
{
    /** @param array<string,string> $headers */
    public function __construct(
        private readonly int $statusCode,
        string $message = '',
        private readonly array $headers = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    /** @return array<string,string> */
    public function headers(): array
    {
        return $this->headers;
    }
}
