<?php

declare(strict_types=1);

namespace App\Http;

/**
 * Immutable-ish wrapper over the PHP superglobals.
 *
 * Controllers read input through this and never touch $_GET / $_POST directly,
 * so there is exactly one place where raw request data enters the application.
 */
final class Request
{
    /** @var array<string,mixed> */
    private array $attributes = [];

    /**
     * @param array<string,mixed> $query
     * @param array<string,mixed> $body
     * @param array<string,mixed> $server
     * @param array<string,string> $cookies
     * @param array<string,mixed> $files
     */
    public function __construct(
        private readonly array $query = [],
        private readonly array $body = [],
        private readonly array $server = [],
        private readonly array $cookies = [],
        private readonly array $files = [],
        private readonly ?string $rawBody = null,
    ) {
    }

    public static function capture(): self
    {
        $raw = file_get_contents('php://input') ?: null;
        $body = $_POST;

        // Accept a JSON body on API routes.
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if ($body === [] && $raw !== null && str_contains($contentType, 'application/json')) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $body = $decoded;
            }
        }

        return new self($_GET, $body, $_SERVER, $_COOKIE, $_FILES, $raw);
    }

    public function method(): string
    {
        $method = strtoupper((string) ($this->server['REQUEST_METHOD'] ?? 'GET'));

        // Browsers only send GET and POST, so forms spoof the rest with a
        // hidden _method field. Only ever honoured on a POST.
        if ($method === 'POST') {
            $spoofed = strtoupper((string) ($this->body['_method'] ?? ''));
            if (in_array($spoofed, ['PUT', 'PATCH', 'DELETE'], true)) {
                return $spoofed;
            }
        }

        return $method;
    }

    public function path(): string
    {
        $uri = (string) ($this->server['REQUEST_URI'] ?? '/');
        $path = parse_url($uri, PHP_URL_PATH);
        $path = is_string($path) ? $path : '/';
        $path = '/' . trim($path, '/');

        return $path === '/' ? '/' : rtrim($path, '/');
    }

    public function uri(): string
    {
        return (string) ($this->server['REQUEST_URI'] ?? '/');
    }

    public function isSecure(): bool
    {
        return ($this->server['HTTPS'] ?? '') === 'on'
            || (int) ($this->server['SERVER_PORT'] ?? 80) === 443
            || strtolower((string) ($this->server['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    /** Trimmed string input — the common case for form fields. */
    public function string(string $key, string $default = ''): string
    {
        $value = $this->input($key, $default);

        return is_scalar($value) ? trim((string) $value) : $default;
    }

    public function boolean(string $key): bool
    {
        return filter_var($this->input($key), FILTER_VALIDATE_BOOL);
    }

    public function integer(string $key, int $default = 0): int
    {
        $value = $this->input($key);

        return is_numeric($value) ? (int) $value : $default;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->body) || array_key_exists($key, $this->query);
    }

    /** @return array<string,mixed> */
    public function all(): array
    {
        return $this->query + $this->body;
    }

    /**
     * @param list<string> $keys
     * @return array<string,mixed>
     */
    public function only(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            if ($this->has($key)) {
                $result[$key] = $this->input($key);
            }
        }

        return $result;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function cookie(string $key, ?string $default = null): ?string
    {
        $value = $this->cookies[$key] ?? $default;

        return is_string($value) ? $value : $default;
    }

    public function header(string $name, ?string $default = null): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        $value = $this->server[$key] ?? $this->server[strtoupper(str_replace('-', '_', $name))] ?? $default;

        return is_string($value) ? $value : $default;
    }

    /** @return array<string,mixed> */
    public function file(string $key): array
    {
        $file = $this->files[$key] ?? [];

        return is_array($file) ? $file : [];
    }

    public function ip(): string
    {
        // REMOTE_ADDR only. Forwarded headers are trivially spoofed and are not
        // trusted unless a known proxy is configured in front of the app.
        $ip = (string) ($this->server['REMOTE_ADDR'] ?? '0.0.0.0');

        return filter_var($ip, FILTER_VALIDATE_IP) !== false ? $ip : '0.0.0.0';
    }

    /** Packed binary IP for the VARBINARY(16) columns. */
    public function ipBinary(): string
    {
        $packed = @inet_pton($this->ip());

        return $packed === false ? inet_pton('0.0.0.0') : $packed;
    }

    public function userAgent(): string
    {
        return substr((string) ($this->server['HTTP_USER_AGENT'] ?? ''), 0, 255);
    }

    public function expectsJson(): bool
    {
        $accept = (string) ($this->server['HTTP_ACCEPT'] ?? '');

        return str_contains($accept, 'application/json')
            || str_starts_with($this->path(), '/api/')
            || $this->header('X-Requested-With') === 'XMLHttpRequest';
    }

    public function isMethod(string $method): bool
    {
        return $this->method() === strtoupper($method);
    }

    /**
     * The unparsed request body.
     *
     * Payment webhooks are signed over the exact bytes sent, so the signature
     * must be verified against this and never against a re-encoded array.
     */
    public function rawBody(): ?string
    {
        return $this->rawBody;
    }

    /** Route parameters and other per-request state set by middleware. */
    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function attribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    public function route(string $key, mixed $default = null): mixed
    {
        $parameters = $this->attributes['route_parameters'] ?? [];

        return is_array($parameters) ? ($parameters[$key] ?? $default) : $default;
    }
}
