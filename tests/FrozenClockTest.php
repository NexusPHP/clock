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

use Nexus\Clock\FrozenClock;
use Nexus\Clock\InvalidDurationException;
use Nexus\Clock\Microseconds;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(FrozenClock::class)]
final class FrozenClockTest extends TestCase
{
    public function testDefaultEpochIsDeterministic(): void
    {
        $clock = new FrozenClock();

        self::assertSame('2020-01-01T00:00:00+00:00', $clock->now()->format(\DATE_ATOM));
        self::assertSame('2020-01-01T00:00:00+00:00', (new FrozenClock())->now()->format(\DATE_ATOM));
    }

    public function testNowFromString(): void
    {
        $clock = new FrozenClock('2024-08-05 18:00:00+08:00');

        self::assertSame('2024-08-05 18:00:00', $clock->now()->format('Y-m-d H:i:s'));
        self::assertSame('+08:00', $clock->now()->getTimezone()->getName());
    }

    public function testBareStringIsInterpretedAsUtc(): void
    {
        $previous = date_default_timezone_get();
        date_default_timezone_set('America/New_York');

        try {
            $clock = new FrozenClock('2024-08-05 18:00:00');
        } finally {
            date_default_timezone_set($previous);
        }

        self::assertSame('UTC', $clock->now()->getTimezone()->getName());
        self::assertSame(1_722_880_800, $clock->now()->getTimestamp());
    }

    public function testNowFromDateTimeImmutable(): void
    {
        $now = new \DateTimeImmutable('2024-08-05 18:00:00');

        self::assertSame($now, (new FrozenClock($now))->now());
    }

    public function testNowIsFrozenBetweenReadings(): void
    {
        $clock = new FrozenClock();

        self::assertSame($clock->now(), $clock->now());
    }

    public function testReadStartsAtZero(): void
    {
        self::assertSame(0.0, (new FrozenClock())->read());
    }

    public function testSleepAdvancesBothReadings(): void
    {
        $clock = new FrozenClock('2024-08-05 18:00:00+08:00');

        $clock->sleep(3.140592);

        self::assertSame('2024-08-05 18:00:03.140592', $clock->now()->format('Y-m-d H:i:s.u'));
        self::assertSame('+08:00', $clock->now()->getTimezone()->getName());
        self::assertSame(3.140592, $clock->read());
    }

    public function testPreEpochInstantsAdvanceCorrectly(): void
    {
        $clock = new FrozenClock('1969-12-31 23:59:59.500000+00:00');

        $clock->sleep(1);

        self::assertSame('1970-01-01 00:00:00.500000', $clock->now()->format('Y-m-d H:i:s.u'));
        self::assertSame(1.0, $clock->read());
    }

    public function testSubMicrosecondSleepsAdvanceNeitherReading(): void
    {
        $clock = new FrozenClock();

        $clock->sleep(0.0000004);

        self::assertSame('2020-01-01 00:00:00.000000', $clock->now()->format('Y-m-d H:i:s.u'));
        self::assertSame(0.0, $clock->read());
    }

    public function testSleepRoundsHalfAMicrosecondUpInBothReadings(): void
    {
        $clock = new FrozenClock();

        $clock->sleep(0.0000006);

        self::assertSame('2020-01-01 00:00:00.000001', $clock->now()->format('Y-m-d H:i:s.u'));
        self::assertSame(0.000001, $clock->read());
    }

    public function testFloatNoiseDoesNotAccumulateAcrossSleeps(): void
    {
        $clock = new FrozenClock();

        $clock->sleep(0.1);
        $clock->sleep(0.2);

        self::assertSame('2020-01-01 00:00:00.300000', $clock->now()->format('Y-m-d H:i:s.u'));
        self::assertSame(0.3, $clock->read());
    }

    public function testWholeSecondsSplitFromMicrosecondsExactly(): void
    {
        $clock = new FrozenClock();

        $clock->sleep(999.9995);

        self::assertSame('2020-01-01 00:16:39.999500', $clock->now()->format('Y-m-d H:i:s.u'));
        self::assertSame(999.9995, $clock->read());
    }

    public function testAdvanceBehavesIdenticallyToSleep(): void
    {
        $slept = new FrozenClock();
        $advanced = new FrozenClock();

        $slept->sleep(2);
        $advanced->advance(2);

        self::assertSame($slept->now()->format('Y-m-d\TH:i:s.uP'), $advanced->now()->format('Y-m-d\TH:i:s.uP'));
        self::assertSame($slept->read(), $advanced->read());
        self::assertSame('2020-01-01T00:00:02+00:00', $advanced->now()->format(\DATE_ATOM));
    }

    public function testSuccessiveSleepsAccumulate(): void
    {
        $clock = new FrozenClock();

        $clock->sleep(1.5);
        $clock->sleep(2);

        self::assertSame('2020-01-01T00:00:03.500000', $clock->now()->format('Y-m-d\TH:i:s.u'));
        self::assertSame(3.5, $clock->read());
    }

    #[DataProvider('provideNonPositiveSecondsDoNotAdvanceCases')]
    public function testNonPositiveSecondsDoNotAdvance(float|int $seconds): void
    {
        $clock = new FrozenClock();
        $before = $clock->now();

        $clock->sleep($seconds);
        $clock->advance($seconds);

        self::assertSame($before, $clock->now());
        self::assertSame(0.0, $clock->read());
    }

    /**
     * @return iterable<string, array{float|int}>
     */
    public static function provideNonPositiveSecondsDoNotAdvanceCases(): iterable
    {
        yield 'zero' => [0];

        yield 'negative int' => [-1];

        yield 'negative float' => [-0.5];
    }

    #[DataProvider('provideInvalidDurationsAreRefusedCases')]
    public function testInvalidDurationsAreRefused(float $seconds, string $message): void
    {
        $this->expectException(InvalidDurationException::class);
        $this->expectExceptionMessageMatches('/'.preg_quote($message, '/').'/');

        (new FrozenClock())->sleep($seconds);
    }

    /**
     * @return iterable<string, array{float, string}>
     */
    public static function provideInvalidDurationsAreRefusedCases(): iterable
    {
        yield 'not a number' => [\NAN, 'Invalid duration of NAN seconds.'];

        yield 'positive infinity' => [\INF, 'Invalid duration of INF seconds.'];

        yield 'beyond the microsecond range' => [1e13, 'Invalid duration of 10000000000000.0 seconds.'];

        yield 'just past the bound' => [9_000_000_000_001.0, 'Invalid duration of 9000000000001.0 seconds.'];
    }

    public function testMaximumSecondsFillTheAccumulatorExactly(): void
    {
        $clock = new FrozenClock();

        $clock->sleep(Microseconds::MAX_SECONDS);

        self::assertSame(9_000_000_000_000.0, $clock->read());
    }

    public function testCumulativeAdvancesBeyondTheMicrosecondRangeAreRefused(): void
    {
        $clock = new FrozenClock();
        $clock->sleep(Microseconds::MAX_SECONDS);

        $before = $clock->now();
        $reading = $clock->read();

        try {
            $clock->sleep(Microseconds::MAX_SECONDS);
            self::fail('An InvalidDurationException was expected.');
        } catch (InvalidDurationException $e) {
            self::assertSame('Cannot advance the clock by 9000000000000.0 more seconds.', $e->getMessage());
        }

        self::assertSame($before, $clock->now());
        self::assertSame($reading, $clock->read());
    }
}
