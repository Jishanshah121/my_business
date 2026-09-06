<?php

declare(strict_types=1);

namespace App\Http;

class Response
{
    /** @var array<string,string> */
    protected array $headers = [];

    /** @var list<array{0:string,1:string,2:array<string,mixed>}> */
    protected array $cookies = [];

    /** @param array<string,string> $headers */
    public function __construct(
        protected string $content = '',
        protected int $status = 200,
        array $headers = [],
    ) {
        foreach ($headers as $name => $value) {
            $this->headers[$name] = $value;
        }
    }

    public function status(): int
    {
        return $this->status;
    }

    public function content(): string
    {
        return $this->content;
    }

    public function setStatus(int $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function header(string $name, string $value): static
    {
        $this->headers[$name] = $value;

        return $this;
    }

    /** @return array<string,string> */
    public function headers(): array
    {
        return $this->headers;
    }

    /** @param array<string,mixed> $options */
    public function cookie(string $name, string $value, array $options = []): static
    {
        $this->cookies[] = [$name, $value, $options];

        return $this;
    }

    public function send(): void
    {
        if (!headers_sent()) {
            http_response_code($this->status);

            foreach ($this->headers as $name => $value) {
                header("{$name}: {$value}", true);
            }

            foreach ($this->cookies as [$name, $value, $options]) {
                setcookie($name, $value, $options);
            }
        }

        echo $this->content;
    }
}
