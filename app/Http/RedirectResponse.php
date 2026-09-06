<?php

declare(strict_types=1);

namespace App\Http;

final class RedirectResponse extends Response
{
    public function __construct(string $location, int $status = 302)
    {
        parent::__construct('', $status, ['Location' => $location]);
    }
}
