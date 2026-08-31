<?php

declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;

/**
 * Money as integer minor units (paise). Immutable.
 *
 * Money NEVER touches a float in this application. Catalog unit prices are
 * stored as DECIMAL(12,4) because sub-paise per-piece rates are real
 * (Rs 0.4750/piece); totals are DECIMAL(12,2). All arithmetic happens here
 * in integers and rounding is half-up, applied once per line and then summed.
 */
final class Money
{
    private function __construct(
        public readonly int $paise,
        public readonly string $currency = 'INR',
    ) {
    }

    public static function fromPaise(int $paise, string $currency = 'INR'): self
    {
        return new self($paise, $currency);
    }

    public static function fromRupees(int|string $rupees, string $currency = 'INR'): self
    {
        return new self((int) round((float) $rupees * 100), $currency);
    }

    /** Parse a DECIMAL string from the database without float drift. */
    public static function fromDecimalString(string $value, string $currency = 'INR'): self
    {
        $negative = str_starts_with($value, '-');
        $value = ltrim($value, '+-');

        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '0');
        $fraction = substr(str_pad($fraction, 3, '0'), 0, 3);

        $paise = (int) $whole * 100 + (int) substr($fraction, 0, 2);
        // Half-up on the third decimal place.
        if ((int) $fraction[2] >= 5) {
            $paise++;
        }

        return new self($negative ? -$paise : $paise, $currency);
    }

    public static function zero(string $currency = 'INR'): self
    {
        return new self(0, $currency);
    }

    public function add(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->paise + $other->paise, $this->currency);
    }

    public function subtract(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->paise - $other->paise, $this->currency);
    }

    /** Multiply by a whole quantity — the common case for order lines. */
    public function multiply(int $quantity): self
    {
        return new self($this->paise * $quantity, $this->currency);
    }

    /**
     * Multiply by a rate (a percentage or fractional multiplier), rounding
     * half-up. Used for tax and percentage discounts.
     */
    public function multiplyByRate(string $rate): self
    {
        $scaled = (int) round($this->paise * (float) $rate, 0, PHP_ROUND_HALF_UP);

        return new self($scaled, $this->currency);
    }

    public function percentage(string $percent): self
    {
        return $this->multiplyByRate(bcdiv_safe($percent, '100'));
    }

    /**
     * Split into n parts whose sum equals the original exactly. Used to
     * allocate a cart-level coupon across lines without losing paise.
     *
     * @param list<int> $weights
     * @return list<self>
     */
    public function allocate(array $weights): array
    {
        $total = array_sum($weights);
        if ($total <= 0) {
            throw new InvalidArgumentException('Allocation weights must sum to a positive number.');
        }

        $remainder = $this->paise;
        $shares = [];

        foreach ($weights as $weight) {
            $share = intdiv($this->paise * $weight, $total);
            $shares[] = $share;
            $remainder -= $share;
        }

        // Distribute the rounding remainder one paisa at a time, largest weight first.
        $order = array_keys($weights);
        usort($order, static fn (int $a, int $b): int => $weights[$b] <=> $weights[$a]);

        $i = 0;
        while ($remainder > 0 && $order !== []) {
            $shares[$order[$i % count($order)]]++;
            $remainder--;
            $i++;
        }

        return array_map(fn (int $p): self => new self($p, $this->currency), $shares);
    }

    public function isZero(): bool
    {
        return $this->paise === 0;
    }

    public function isNegative(): bool
    {
        return $this->paise < 0;
    }

    public function lessThan(self $other): bool
    {
        $this->assertSameCurrency($other);

        return $this->paise < $other->paise;
    }

    public function equals(self $other): bool
    {
        return $this->currency === $other->currency && $this->paise === $other->paise;
    }

    public static function min(self ...$values): self
    {
        $min = array_shift($values);
        if ($min === null) {
            throw new InvalidArgumentException('Money::min() requires at least one value.');
        }

        foreach ($values as $value) {
            if ($value->lessThan($min)) {
                $min = $value;
            }
        }

        return $min;
    }

    /** DECIMAL(12,2) string for storage. */
    public function toDecimalString(): string
    {
        $sign = $this->paise < 0 ? '-' : '';
        $abs = abs($this->paise);

        return sprintf('%s%d.%02d', $sign, intdiv($abs, 100), $abs % 100);
    }

    /** Human display, e.g. "Rs 1,149.00" rendered with the configured symbol. */
    public function format(bool $withSymbol = true): string
    {
        $symbol = $withSymbol ? (string) Config::get('app.currency.symbol', '₹') : '';
        $sign = $this->paise < 0 ? '-' : '';
        $abs = abs($this->paise);

        $whole = number_format_indian(intdiv($abs, 100));

        return sprintf('%s%s%s.%02d', $sign, $symbol, $whole, $abs % 100);
    }

    public function __toString(): string
    {
        return $this->toDecimalString();
    }

    private function assertSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException(
                "Cannot combine {$this->currency} with {$other->currency}."
            );
        }
    }
}

/** Indian digit grouping: 12,34,567 rather than 1,234,567. */
function number_format_indian(int $number): string
{
    $string = (string) $number;
    if (strlen($string) <= 3) {
        return $string;
    }

    $last3 = substr($string, -3);
    $rest = substr($string, 0, -3);
    $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest) ?? $rest;

    return $rest . ',' . $last3;
}

/** Division that avoids float representation error for percentage maths. */
function bcdiv_safe(string $a, string $b): string
{
    if (function_exists('bcdiv')) {
        return bcdiv($a, $b, 10);
    }

    return (string) ((float) $a / (float) $b);
}
