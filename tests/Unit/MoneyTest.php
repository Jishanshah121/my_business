<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Money;
use PHPUnit\Framework\TestCase;

/**
 * Money is integer paise throughout. These tests exist because a rounding
 * error here becomes a rupee difference between the cart, the order and the
 * GST invoice — three documents that must agree exactly.
 */
final class MoneyTest extends TestCase
{
    public function testConstructionFromRupeesAndPaise(): void
    {
        self::assertSame(10000, Money::fromRupees(100)->paise);
        self::assertSame(0, Money::zero()->paise);
        self::assertSame(2500, Money::fromPaise(2500)->paise);
    }

    public function testDecimalStringParsingIsExactAndRoundsHalfUp(): void
    {
        self::assertSame(3800, Money::fromDecimalString('38.00')->paise);
        self::assertSame(3800, Money::fromDecimalString('38')->paise);
        self::assertSame(48, Money::fromDecimalString('0.4750')->paise, '0.4750 rounds half-up to 0.48');
        self::assertSame(47, Money::fromDecimalString('0.4749')->paise);
        self::assertSame(-1250, Money::fromDecimalString('-12.50')->paise);
    }

    public function testArithmeticStaysInIntegers(): void
    {
        $a = Money::fromRupees(100);
        $b = Money::fromPaise(2550);

        self::assertSame(12550, $a->add($b)->paise);
        self::assertSame(7450, $a->subtract($b)->paise);
        self::assertSame(25500, $b->multiply(10)->paise);
    }

    /**
     * The classic float failure: 0.1 + 0.2 !== 0.3. Repeated over an order of
     * 5,000 pieces it becomes a visible discrepancy.
     */
    public function testNoFloatDriftOverManyLines(): void
    {
        $total = Money::zero();
        for ($i = 0; $i < 10000; $i++) {
            $total = $total->add(Money::fromPaise(10));
        }

        self::assertSame(100000, $total->paise);
        self::assertSame('1000.00', $total->toDecimalString());
    }

    public function testGstRateAppliesHalfUp(): void
    {
        // 18% of Rs 149.00 = Rs 26.82
        self::assertSame(2682, Money::fromRupees(149)->multiplyByRate('0.18')->paise);
        // 5% of Rs 0.99 = 4.95p -> 5p
        self::assertSame(5, Money::fromPaise(99)->multiplyByRate('0.05')->paise);
    }

    /**
     * Allocation must be lossless: a cart coupon split across lines has to sum
     * back to exactly the discount granted, or the GST base is wrong.
     */
    public function testAllocationIsLossless(): void
    {
        $discount = Money::fromPaise(10000);
        $shares = $discount->allocate([3, 3, 3]);

        self::assertCount(3, $shares);
        self::assertSame(10000, array_sum(array_map(static fn (Money $m): int => $m->paise, $shares)));
        self::assertSame([3334, 3333, 3333], array_map(static fn (Money $m): int => $m->paise, $shares));
    }

    public function testAllocationRespectsWeights(): void
    {
        $shares = Money::fromPaise(1000)->allocate([1, 4]);

        self::assertSame(1000, $shares[0]->paise + $shares[1]->paise);
        self::assertSame(200, $shares[0]->paise);
        self::assertSame(800, $shares[1]->paise);
    }

    public function testAllocationRejectsNonPositiveWeights(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Money::fromPaise(100)->allocate([0, 0]);
    }

    public function testDecimalStringIsSafeForStorage(): void
    {
        self::assertSame('149.00', Money::fromRupees(149)->toDecimalString());
        self::assertSame('0.05', Money::fromPaise(5)->toDecimalString());
        self::assertSame('-12.50', Money::fromPaise(-1250)->toDecimalString());
    }

    public function testMinPicksTheLowestCandidate(): void
    {
        $lowest = Money::min(Money::fromPaise(500), Money::fromPaise(120), Money::fromPaise(340));

        self::assertSame(120, $lowest->paise);
    }

    public function testMixingCurrenciesIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Money::fromPaise(100, 'INR')->add(Money::fromPaise(100, 'USD'));
    }
}
