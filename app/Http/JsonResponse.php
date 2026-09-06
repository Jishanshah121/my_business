<?php

declare(strict_types=1);

namespace App\Http;

final class JsonResponse extends Response
{
    /**
     * @param array<string,mixed>|list<mixed> $data
     * @param array<string,string> $headers
     */
    public function __construct(array $data = [], int $status = 200, array $headers = [])
    {
        parent::__construct(
            json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{}',
            $status,
            $headers + ['Content-Type' => 'application/json; charset=utf-8']
        );
    }

    /**
     * The API envelope from the architecture document.
     *
     * @param array<string,mixed>|list<mixed> $data
     * @param array<string,mixed> $meta
     */
    public static function ok(array $data, array $meta = [], int $status = 200): self
    {
        return new self(['data' => $data, 'meta' => (object) $meta, 'errors' => []], $status);
    }

    /** @param array<string,list<string>>|list<string> $errors */
    public static function error(string $message, array $errors = [], int $status = 422): self
    {
        return new self(
            ['data' => null, 'meta' => (object) [], 'errors' => ['message' => $message, 'fields' => $errors]],
            $status
        );
    }
}
