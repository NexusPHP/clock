<?php

declare(strict_types=1);

/**
 * This file is part of Nexus Clock.
 *
 * (c) 2026 John Paul E. Balandan, CPA <paulbalandan@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Nexus\Clock\Tests;

use Nexus\Clock\SystemClock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(SystemClock::class)]
final class SystemClockTest extends TestCase
{
    public function testTimezoneDefaultsToUtc(): void
    {
        self::assertSame('UTC', (new SystemClock())->now()->getTimezone()->getName());
    }

    #[DataProvider('provideNowUsesTheGivenTimezoneCases')]
    public function testNowUsesTheGivenTimezone(\DateTimeZone|string $timezone): void
    {
        self::assertSame('Asia/Manila', (new SystemClock($timezone))->now()->getTimezone()->getName());
    }

    /**
     * @return iterable<string, array{\DateTimeZone|string}>
     */
    public static function provideNowUsesTheGivenTimezoneCases(): iterable
    {
        yield 'string' => ['Asia/Manila'];

        yield 'object' => [new \DateTimeZone('Asia/Manila')];
    }

    public function testNowTracksTheSystemTime(): void
    {
        $before = time();
        $timestamp = (new SystemClock())->now()->getTimestamp();
        $after = time();

        self::assertGreaterThanOrEqual($before, $timestamp);
        self::assertLessThanOrEqual($after, $timestamp);
    }

    public function testNowMovesForwardBetweenReadings(): void
    {
        $clock = new SystemClock();

        $first = $clock->now();
        usleep(10_000);
        $second = $clock->now();

        self::assertGreaterThan($first, $second);
    }
}
