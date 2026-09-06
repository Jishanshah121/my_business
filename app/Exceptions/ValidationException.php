<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class ValidationException extends RuntimeException
{
    /**
     * @param array<string,list<string>> $errors field => messages
     * @param array<string,mixed> $old Submitted values, minus anything secret.
     */
    public function __construct(
        private readonly array $errors,
        private readonly array $old = [],
        string $message = 'Please correct the highlighted fields.',
    ) {
        parent::__construct($message);
    }

    /** @return array<string,list<string>> */
    public function errors(): array
    {
        return $this->errors;
    }

    /** @return array<string,mixed> */
    public function old(): array
    {
        return $this->old;
    }
}
