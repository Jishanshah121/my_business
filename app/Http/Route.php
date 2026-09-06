<?php

declare(strict_types=1);

namespace App\Http;

final class Route
{
    /** @var list<string> */
    private array $middleware = [];

    private ?string $name = null;

    private string $regex;

    /** @var list<string> */
    private array $parameterNames = [];

    /**
     * @param list<string> $methods
     * @param array{0:class-string,1:string} $action
     */
    public function __construct(
        private readonly array $methods,
        private readonly string $uri,
        private readonly array $action,
    ) {
        $this->compile();
    }

    /**
     * Compile "/products/{slug}" into a regex, capturing parameter names.
     * "{id:\d+}" constrains a parameter.
     *
     * The constraint sub-pattern allows balanced `{n}` and `{n,m}` quantifiers,
     * so "{token:[a-f0-9]{64}}" compiles correctly. Matching only `[^}]+` here
     * silently truncates such a constraint and the route never matches.
     */
    private function compile(): void
    {
        $names = [];
        $pattern = preg_replace_callback(
            '/\{([a-zA-Z_][a-zA-Z0-9_]*)(?::((?:[^{}]++|\{\d+(?:,\d*)?\})++))?\}/',
            static function (array $m) use (&$names): string {
                $names[] = $m[1];
                $constraint = $m[2] ?? '[^/]+';

                return '(' . $constraint . ')';
            },
            $this->uri
        ) ?? $this->uri;

        $this->parameterNames = $names;
        $this->regex = '#^' . $pattern . '$#';
    }

    /**
     * @return array<string,string>|null Parameters, or null when no match.
     */
    public function match(string $method, string $path): ?array
    {
        if (!in_array($method, $this->methods, true)) {
            return null;
        }

        if (preg_match($this->regex, $path, $matches) !== 1) {
            return null;
        }

        array_shift($matches);

        return array_combine($this->parameterNames, $matches) ?: [];
    }

    /** Does the URI pattern match, ignoring the method? Used for 405s. */
    public function matchesPath(string $path): bool
    {
        return preg_match($this->regex, $path) === 1;
    }

    public function middleware(string ...$names): self
    {
        foreach ($names as $name) {
            $this->middleware[] = $name;
        }

        return $this;
    }

    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /** @return list<string> */
    public function middlewareNames(): array
    {
        return $this->middleware;
    }

    public function routeName(): ?string
    {
        return $this->name;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    /** @return list<string> */
    public function methods(): array
    {
        return $this->methods;
    }

    /** @return array{0:class-string,1:string} */
    public function action(): array
    {
        return $this->action;
    }
}
