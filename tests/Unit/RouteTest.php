<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RouteTest extends TestCase
{
    /**
     * Regression: a constraint containing a `{n}` quantifier used to truncate
     * at the first closing brace, so "/verify-email/{token:[a-f0-9]{64}}"
     * compiled to a pattern that never matched and every emailed verification
     * and reset link 404'd.
     */
    #[DataProvider('patternProvider')]
    public function testUriPatternsCompileAndMatch(string $uri, string $path, bool $shouldMatch): void
    {
        $route = new Route(['GET'], $uri, [self::class, 'noop']);

        self::assertSame($shouldMatch, $route->match('GET', $path) !== null);
    }

    public static function patternProvider(): array
    {
        $token = str_repeat('a1b2', 16); // 64 hex characters

        return [
            'quantified constraint matches'   => ['/verify-email/{token:[a-f0-9]{64}}', "/verify-email/{$token}", true],
            'quantified constraint too short' => ['/verify-email/{token:[a-f0-9]{64}}', '/verify-email/abc', false],
            'quantified constraint bad chars' => ['/verify-email/{token:[a-f0-9]{64}}', '/verify-email/' . str_repeat('z', 64), false],
            'range quantifier'                => ['/x/{code:[0-9]{2,4}}', '/x/123', true],
            'range quantifier out of range'   => ['/x/{code:[0-9]{2,4}}', '/x/123456', false],
            'digit constraint'                => ['/admin/{id:\d+}/approve', '/admin/42/approve', true],
            'digit constraint rejects text'   => ['/admin/{id:\d+}/approve', '/admin/abc/approve', false],
            'unconstrained parameter'         => ['/products/{slug}', '/products/paper-cup-90ml', true],
            'parameter cannot span segments'  => ['/products/{slug}', '/products/a/b', false],
            'static route'                    => ['/login', '/login', true],
        ];
    }

    public function testParametersAreExtractedByName(): void
    {
        $route = new Route(['GET'], '/orders/{order}/items/{item:\d+}', [self::class, 'noop']);

        self::assertSame(
            ['order' => 'SK-2627-000123', 'item' => '7'],
            $route->match('GET', '/orders/SK-2627-000123/items/7')
        );
    }

    public function testMethodMismatchDoesNotMatch(): void
    {
        $route = new Route(['POST'], '/login', [self::class, 'noop']);

        self::assertNull($route->match('GET', '/login'));
        self::assertTrue($route->matchesPath('/login'), 'path still matches, which is how a 405 is detected');
    }

    public static function noop(): void
    {
    }
}
