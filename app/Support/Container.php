<?php

declare(strict_types=1);

namespace App\Support;

use Closure;
use ReflectionClass;
use ReflectionNamedType;
use RuntimeException;

/**
 * Minimal dependency-injection container with constructor autowiring.
 *
 * Deliberately small (ADR-001: justify every dependency). It resolves
 * constructor type-hints recursively, honours explicit bindings, and caches
 * singletons. That covers everything this application needs; anything more
 * elaborate would be a framework we chose not to adopt.
 */
final class Container
{
    private static ?self $instance = null;

    /** @var array<string,Closure> */
    private array $bindings = [];

    /** @var array<string,object> */
    private array $instances = [];

    /** @var list<string> Guards against circular constructor dependencies. */
    private array $resolving = [];

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    public static function reset(): void
    {
        self::$instance = null;
    }

    /** @var array<string,bool> Bindings whose result is cached after first build. */
    private array $shared = [];

    /** Register a factory. Rebuilt on every get() unless $shared is true. */
    public function bind(string $abstract, Closure $factory, bool $shared = false): void
    {
        $this->bindings[$abstract] = $factory;
        unset($this->instances[$abstract]);

        if ($shared) {
            $this->shared[$abstract] = true;
        } else {
            unset($this->shared[$abstract]);
        }
    }

    /** Register a factory whose result is built once and reused. */
    public function singleton(string $abstract, Closure $factory): void
    {
        $this->bind($abstract, $factory, true);
    }

    /** Register an already-built object. */
    public function instance(string $abstract, object $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    public function has(string $abstract): bool
    {
        return isset($this->instances[$abstract])
            || isset($this->bindings[$abstract])
            || class_exists($abstract);
    }

    /** Explicitly registered — as opposed to merely autowirable. */
    public function isBound(string $abstract): bool
    {
        return isset($this->instances[$abstract]) || isset($this->bindings[$abstract]);
    }

    /**
     * @template T of object
     * @param class-string<T> $abstract
     * @return T
     */
    public function get(string $abstract): object
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        if (in_array($abstract, $this->resolving, true)) {
            throw new RuntimeException(
                'Circular dependency: ' . implode(' -> ', [...$this->resolving, $abstract])
            );
        }

        $this->resolving[] = $abstract;

        try {
            $object = isset($this->bindings[$abstract])
                ? ($this->bindings[$abstract])($this)
                : $this->build($abstract);
        } finally {
            array_pop($this->resolving);
        }

        if (isset($this->shared[$abstract]) || !isset($this->bindings[$abstract])) {
            // Concrete classes are shared by default: services in this
            // application are stateless, and rebuilding them per call would
            // reopen database handles for no benefit.
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    private function build(string $class): object
    {
        if (!class_exists($class)) {
            throw new RuntimeException("Cannot resolve [{$class}] — class does not exist.");
        }

        $reflection = new ReflectionClass($class);
        if (!$reflection->isInstantiable()) {
            throw new RuntimeException("Cannot resolve [{$class}] — it is not instantiable. Bind an implementation.");
        }

        $constructor = $reflection->getConstructor();
        if ($constructor === null) {
            return new $class();
        }

        $arguments = [];
        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();
            $isClass = $type instanceof ReflectionNamedType && !$type->isBuiltin();

            // An optional class dependency (`?PDO $pdo = null`) takes its
            // default unless something is explicitly bound for it. Repositories
            // use that shape to accept an injected connection in tests while
            // resolving their own in production.
            if ($parameter->isDefaultValueAvailable()
                && (!$isClass || !$this->isBound($type->getName()))) {
                $arguments[] = $parameter->getDefaultValue();
                continue;
            }

            if ($isClass) {
                $arguments[] = $this->get($type->getName());
                continue;
            }

            throw new RuntimeException(
                "Cannot resolve parameter \${$parameter->getName()} of [{$class}] — no type hint and no default."
            );
        }

        return $reflection->newInstanceArgs($arguments);
    }

    /**
     * Call a method, resolving its type-hinted parameters from the container.
     *
     * @param array<string,mixed> $extra Values matched by parameter name.
     */
    public function call(object $object, string $method, array $extra = []): mixed
    {
        $reflection = new ReflectionClass($object);
        if (!$reflection->hasMethod($method)) {
            throw new RuntimeException(get_class($object) . " has no method {$method}().");
        }

        $arguments = [];
        foreach ($reflection->getMethod($method)->getParameters() as $parameter) {
            $name = $parameter->getName();

            if (array_key_exists($name, $extra)) {
                $value = $extra[$name];
                $type = $parameter->getType();
                if ($type instanceof ReflectionNamedType && $type->isBuiltin()) {
                    $typeName = $type->getName();
                    if ($typeName === 'int' && is_numeric($value)) {
                        $value = (int) $value;
                    } elseif ($typeName === 'float' && is_numeric($value)) {
                        $value = (float) $value;
                    } elseif ($typeName === 'bool') {
                        $value = filter_var($value, FILTER_VALIDATE_BOOL);
                    } elseif ($typeName === 'string') {
                        $value = (string) $value;
                    }
                }
                $arguments[] = $value;
                continue;
            }

            $type = $parameter->getType();
            $isClass = $type instanceof ReflectionNamedType && !$type->isBuiltin();

            if ($parameter->isDefaultValueAvailable()
                && (!$isClass || !$this->isBound($type->getName()))) {
                $arguments[] = $parameter->getDefaultValue();
                continue;
            }

            if ($isClass) {
                $arguments[] = $this->get($type->getName());
                continue;
            }

            throw new RuntimeException(
                "Cannot resolve parameter \${$name} of " . get_class($object) . "::{$method}()."
            );
        }

        return $object->{$method}(...$arguments);
    }
}
