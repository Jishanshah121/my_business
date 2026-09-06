<?php

declare(strict_types=1);

namespace App\Support;

use Stringable;
use Throwable;

/**
 * Rotating-file logger with structured context.
 *
 * PSR-3 shaped without the dependency (ADR-001). Deliberately small: the
 * requirement is a daily file, a correlation ID that survives the hop to the
 * Python service, and a redaction pass so a stray context key can never write
 * a password or a token to disk.
 */
final class Logger
{
    public const EMERGENCY = 600;
    public const ALERT     = 550;
    public const CRITICAL  = 500;
    public const ERROR     = 400;
    public const WARNING   = 300;
    public const NOTICE    = 250;
    public const INFO      = 200;
    public const DEBUG     = 100;

    private const LEVELS = [
        'emergency' => self::EMERGENCY, 'alert' => self::ALERT, 'critical' => self::CRITICAL,
        'error' => self::ERROR, 'warning' => self::WARNING, 'notice' => self::NOTICE,
        'info' => self::INFO, 'debug' => self::DEBUG,
    ];

    /**
     * Context keys whose values are replaced before anything is written.
     * Matching is on a normalised substring, so `new_password_confirmation`
     * and `RAZORPAY_KEY_SECRET` are both caught.
     */
    private const REDACT = [
        'password', 'passwd', 'secret', 'token', 'authorization', 'auth',
        'apikey', 'api_key', 'key_secret', 'csrf', 'cookie', 'session',
        'card', 'cvv', 'pan_number', 'otp', 'signature', 'credential',
    ];

    private string $correlationId;

    private int $minimumLevel;

    public function __construct(
        private readonly string $directory,
        string $minimumLevel = 'debug',
        private readonly string $channel = 'app',
    ) {
        $this->minimumLevel = self::LEVELS[strtolower($minimumLevel)] ?? self::DEBUG;
        $this->correlationId = substr(bin2hex(random_bytes(8)), 0, 12);
    }

    public function correlationId(): string
    {
        return $this->correlationId;
    }

    public function withCorrelationId(string $id): void
    {
        $this->correlationId = preg_replace('/[^a-zA-Z0-9\-]/', '', $id) ?: $this->correlationId;
    }

    /** @param array<string,mixed> $context */
    public function emergency(string|Stringable $message, array $context = []): void { $this->log('emergency', $message, $context); }
    /** @param array<string,mixed> $context */
    public function critical(string|Stringable $message, array $context = []): void { $this->log('critical', $message, $context); }
    /** @param array<string,mixed> $context */
    public function error(string|Stringable $message, array $context = []): void { $this->log('error', $message, $context); }
    /** @param array<string,mixed> $context */
    public function warning(string|Stringable $message, array $context = []): void { $this->log('warning', $message, $context); }
    /** @param array<string,mixed> $context */
    public function notice(string|Stringable $message, array $context = []): void { $this->log('notice', $message, $context); }
    /** @param array<string,mixed> $context */
    public function info(string|Stringable $message, array $context = []): void { $this->log('info', $message, $context); }
    /** @param array<string,mixed> $context */
    public function debug(string|Stringable $message, array $context = []): void { $this->log('debug', $message, $context); }

    /** @param array<string,mixed> $context */
    public function log(string $level, string|Stringable $message, array $context = []): void
    {
        $severity = self::LEVELS[strtolower($level)] ?? self::INFO;
        if ($severity < $this->minimumLevel) {
            return;
        }

        if (!is_dir($this->directory) && !@mkdir($this->directory, 0775, true) && !is_dir($this->directory)) {
            return;
        }

        $record = [
            'time'    => date('c'),
            'level'   => strtoupper($level),
            'channel' => $this->channel,
            'cid'     => $this->correlationId,
            'message' => (string) $message,
        ];

        $context = $this->redact($context);
        if ($context !== []) {
            $record['context'] = $context;
        }

        $line = json_encode($record, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        $file = $this->directory . '/' . $this->channel . '-' . date('Y-m-d') . '.log';

        @file_put_contents($file, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    /** @param array<string,mixed> $context */
    public function exception(Throwable $e, string $level = 'error', array $context = []): void
    {
        $this->log($level, $e->getMessage(), $context + [
            'exception' => $e::class,
            'file'      => $e->getFile() . ':' . $e->getLine(),
            // Trace without arguments: argument values routinely contain
            // passwords and tokens.
            'trace'     => array_slice(explode("\n", $e->getTraceAsString()), 0, 12),
        ]);
    }

    /**
     * @param array<string,mixed> $context
     * @return array<string,mixed>
     */
    private function redact(array $context, int $depth = 0): array
    {
        if ($depth > 6) {
            return ['_truncated' => true];
        }

        $clean = [];
        foreach ($context as $key => $value) {
            $normalised = strtolower(str_replace(['-', '_', ' '], '', (string) $key));

            $isSecret = false;
            foreach (self::REDACT as $needle) {
                if (str_contains($normalised, str_replace('_', '', $needle))) {
                    $isSecret = true;
                    break;
                }
            }

            if ($isSecret) {
                $clean[$key] = '[redacted]';
                continue;
            }

            $clean[$key] = match (true) {
                is_array($value)  => $this->redact($value, $depth + 1),
                is_scalar($value) || $value === null => $value,
                $value instanceof Throwable => $value::class . ': ' . $value->getMessage(),
                is_object($value) => method_exists($value, '__toString') ? (string) $value : $value::class,
                default           => gettype($value),
            };
        }

        return $clean;
    }
}
