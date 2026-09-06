<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Support\RateLimiter;

final class RateLimiterTest extends DatabaseTestCase
{
    private RateLimiter $limiter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->limiter = new RateLimiter($this->pdo);
    }

    /** config/security.php sets login to 5 attempts per 300 seconds. */
    public function testAllowsExactlyTheConfiguredNumberOfAttempts(): void
    {
        $key = 'user@example.test';

        for ($i = 1; $i <= 5; $i++) {
            self::assertTrue($this->limiter->hit('login', $key), "attempt {$i} should be allowed");
        }

        self::assertFalse($this->limiter->hit('login', $key), 'the 6th attempt must be blocked');
        self::assertTrue($this->limiter->tooManyAttempts('login', $key));
    }

    public function testDifferentIdentifiersAreCountedSeparately(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->limiter->hit('login', 'first@example.test');
        }

        self::assertFalse($this->limiter->hit('login', 'first@example.test'));
        self::assertTrue(
            $this->limiter->hit('login', 'second@example.test'),
            'blocking one account must not block another'
        );
    }

    /**
     * Regression: route middleware and AuthService both throttle logins. If
     * they share a key the effective limit is halved, so the middleware
     * namespaces its key by route.
     */
    public function testRouteNamespacedKeysDoNotCollideWithServiceKeys(): void
    {
        $ip = 'ip:127.0.0.1';
        $routeKey = 'route:/login|' . $ip;

        for ($i = 0; $i < 5; $i++) {
            self::assertTrue($this->limiter->hit('login', $routeKey));
        }
        self::assertFalse($this->limiter->hit('login', $routeKey));

        // The service-level counter for the same IP is untouched.
        self::assertTrue($this->limiter->hit('login', $ip), 'service counter must be independent');
    }

    public function testClearResetsTheCounterAfterASuccessfulSignIn(): void
    {
        $key = 'user@example.test';

        for ($i = 0; $i < 5; $i++) {
            $this->limiter->hit('login', $key);
        }
        self::assertTrue($this->limiter->tooManyAttempts('login', $key));

        $this->limiter->clear('login', $key);

        self::assertFalse($this->limiter->tooManyAttempts('login', $key));
        self::assertTrue($this->limiter->hit('login', $key));
    }

    public function testAvailableInReportsSecondsRemaining(): void
    {
        $key = 'user@example.test';
        for ($i = 0; $i < 6; $i++) {
            $this->limiter->hit('login', $key);
        }

        $seconds = $this->limiter->availableIn('login', $key);

        self::assertGreaterThan(0, $seconds);
        self::assertLessThanOrEqual(300, $seconds);
    }

    public function testIdentifiersAreHashedNotStoredInTheClear(): void
    {
        $email = 'sensitive@example.test';
        $this->limiter->hit('login', $email);

        $rows = $this->pdo->query('SELECT `key_hash` FROM `rate_limits`')->fetchAll(\PDO::FETCH_COLUMN);

        self::assertNotEmpty($rows);
        foreach ($rows as $stored) {
            self::assertStringNotContainsString($email, (string) $stored);
            self::assertSame(64, strlen((string) $stored), 'stored key should be a SHA-256 hex digest');
        }
    }

    public function testUnknownActionFallsBackToTheDefaultLimit(): void
    {
        // api_default is 120/60, so a handful of hits must all pass.
        for ($i = 0; $i < 10; $i++) {
            self::assertTrue($this->limiter->hit('no_such_action', 'x'));
        }
    }
}
