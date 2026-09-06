<?php

declare(strict_types=1);

namespace App\Http;

use App\Exceptions\HttpException;

final class Router
{
    /** @var list<Route> */
    private array $routes = [];

    /** @var array<string,Route> */
    private array $named = [];

    /** @var array{prefix:string,middleware:list<string>} */
    private array $group = ['prefix' => '', 'middleware' => []];

    /** @param array{0:class-string,1:string} $action */
    public function get(string $uri, array $action): Route
    {
        return $this->add(['GET', 'HEAD'], $uri, $action);
    }

    /** @param array{0:class-string,1:string} $action */
    public function post(string $uri, array $action): Route
    {
        return $this->add(['POST'], $uri, $action);
    }

    /** @param array{0:class-string,1:string} $action */
    public function put(string $uri, array $action): Route
    {
        return $this->add(['PUT'], $uri, $action);
    }

    /** @param array{0:class-string,1:string} $action */
    public function patch(string $uri, array $action): Route
    {
        return $this->add(['PATCH'], $uri, $action);
    }

    /** @param array{0:class-string,1:string} $action */
    public function delete(string $uri, array $action): Route
    {
        return $this->add(['DELETE'], $uri, $action);
    }

    /**
     * @param list<string> $methods
     * @param array{0:class-string,1:string} $action
     */
    public function add(array $methods, string $uri, array $action): Route
    {
        $uri = rtrim($this->group['prefix'] . '/' . trim($uri, '/'), '/');
        if ($uri === '') {
            $uri = '/';
        }

        $route = new Route($methods, $uri, $action);
        if ($this->group['middleware'] !== []) {
            $route->middleware(...$this->group['middleware']);
        }

        $this->routes[] = $route;

        return $route;
    }

    /**
     * @param array{prefix?:string,middleware?:list<string>} $attributes
     * @param callable(Router):void $callback
     */
    public function group(array $attributes, callable $callback): void
    {
        $previous = $this->group;

        $this->group = [
            'prefix'     => $previous['prefix'] . (isset($attributes['prefix']) ? '/' . trim($attributes['prefix'], '/') : ''),
            'middleware' => [...$previous['middleware'], ...($attributes['middleware'] ?? [])],
        ];

        $callback($this);

        $this->group = $previous;
    }

    /**
     * @return array{0:Route,1:array<string,string>}
     * @throws HttpException 404 when nothing matches, 405 when only the method is wrong.
     */
    public function match(Request $request): array
    {
        $method = $request->method();
        $path = $request->path();
        $pathMatched = false;

        foreach ($this->routes as $route) {
            $parameters = $route->match($method, $path);
            if ($parameters !== null) {
                return [$route, $parameters];
            }
            if ($route->matchesPath($path)) {
                $pathMatched = true;
            }
        }

        if ($pathMatched) {
            throw new HttpException(405, 'That address does not accept ' . $method . ' requests.');
        }

        throw new HttpException(404, 'We could not find that page.');
    }

    /** Build the named-route index. Called once after routes are registered. */
    public function indexNames(): void
    {
        foreach ($this->routes as $route) {
            $name = $route->routeName();
            if ($name !== null) {
                $this->named[$name] = $route;
            }
        }
    }

    /**
     * Generate a URL for a named route.
     *
     * @param array<string,string|int> $parameters
     */
    public function url(string $name, array $parameters = []): string
    {
        if ($this->named === []) {
            $this->indexNames();
        }

        if (!isset($this->named[$name])) {
            throw new \RuntimeException("No route named [{$name}].");
        }

        $uri = $this->named[$name]->uri();
        foreach ($parameters as $key => $value) {
            $uri = preg_replace('/\{' . preg_quote($key, '/') . '(?::[^}]+)?\}/', rawurlencode((string) $value), $uri) ?? $uri;
        }

        return $uri;
    }

    /** @return list<Route> */
    public function routes(): array
    {
        return $this->routes;
    }
}
