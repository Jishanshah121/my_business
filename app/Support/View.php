<?php

declare(strict_types=1);

namespace App\Support;

use RuntimeException;
use Throwable;

/**
 * Plain-PHP template renderer with escaping on by default.
 *
 * No template compiler and no new syntax to learn — but `e()` is the only
 * short echo helper templates are given, and printing an unescaped value takes
 * a deliberate `raw()` call that shows up in review. That is the whole point:
 * XSS should require effort, not vigilance.
 */
final class View
{
    private string $path;

    /** @var array<string,mixed> Data shared with every view. */
    private array $shared = [];

    /** @var array<string,string> Captured sections. */
    private array $sections = [];

    /** @var list<string> Section capture stack. */
    private array $stack = [];

    /**
     * Layout requested by the template currently being evaluated.
     *
     * Set from inside a template via $view->extend(), which static analysis
     * cannot see — hence the explicit annotation.
     *
     * @var string|null
     */
    private ?string $extends = null;

    public function __construct(?string $path = null)
    {
        $this->path = $path ?? dirname(__DIR__, 2) . '/views';
    }

    public function share(string $key, mixed $value): void
    {
        $this->shared[$key] = $value;
    }

    /** @param array<string,mixed> $data */
    public function shareMany(array $data): void
    {
        $this->shared = $data + $this->shared;
    }

    /** @param array<string,mixed> $data */
    public function render(string $template, array $data = []): string
    {
        $this->sections = [];
        $this->stack = [];
        $this->extends = null;

        $content = $this->evaluate($template, $data);

        // Walk up the inheritance chain: a template may extend a layout which
        // itself extends another.
        $guard = 0;
        // @phpstan-ignore-next-line — extend() is called from inside the
        // template that evaluate() just ran, which analysis cannot see.
        while ($this->extends !== null) {
            if (++$guard > 10) {
                throw new RuntimeException('View inheritance nested more than 10 levels deep.');
            }
            $layout = $this->extends;
            $this->extends = null;
            $this->sections['content'] ??= $content;
            $content = $this->evaluate($layout, $data);
        }

        return $content;
    }

    /** @param array<string,mixed> $data */
    private function evaluate(string $template, array $data): string
    {
        $file = $this->path . '/' . str_replace('.', '/', $template) . '.php';

        if (!is_file($file)) {
            throw new RuntimeException("View [{$template}] not found at {$file}.");
        }

        $level = ob_get_level();
        ob_start();

        try {
            (function () use ($file, $data): void {
                // Every template gets $view, whoever rendered it. Without this,
                // templates rendered outside a controller (emails, error pages)
                // would have no way to escape output or extend a layout.
                $view = $this;
                extract($this->shared, EXTR_SKIP);
                extract($data, EXTR_SKIP);
                require $file;
            })();
        } catch (Throwable $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
            throw $e;
        }

        return (string) ob_get_clean();
    }

    /**
     * Render a partial inline.
     *
     * @param array<string,mixed> $data
     */
    public function include(string $template, array $data = []): string
    {
        return $this->evaluate($template, $data);
    }

    public function extend(string $layout): void
    {
        $this->extends = $layout;
    }

    public function start(string $section): void
    {
        $this->stack[] = $section;
        ob_start();
    }

    public function stop(): void
    {
        $section = array_pop($this->stack);
        if ($section === null) {
            throw new RuntimeException('View::stop() called without a matching start().');
        }
        $this->sections[$section] = (string) ob_get_clean();
    }

    public function section(string $name, string $default = ''): string
    {
        return $this->sections[$name] ?? $default;
    }

    public function hasSection(string $name): bool
    {
        return isset($this->sections[$name]) && trim($this->sections[$name]) !== '';
    }

    /** Escape for HTML text and attribute contexts. */
    public function e(mixed $value): string
    {
        if ($value === null || is_bool($value)) {
            return '';
        }

        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Print a value without escaping.
     *
     * Only ever call this with markup this application generated. Never with
     * anything that came from a request, a customer or an AI response.
     */
    public function raw(?string $html): string
    {
        return $html ?? '';
    }

    /** JSON for a data-* attribute or an inline script payload. */
    public function json(mixed $value): string
    {
        return htmlspecialchars(
            json_encode($value, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?: 'null',
            ENT_QUOTES,
            'UTF-8'
        );
    }
}
