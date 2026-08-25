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

use Nexus\Clock\InvalidDurationException;
use Nexus\Clock\Microseconds;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Microseconds::class)]
final class MicrosecondsTest extends TestCase
{
    #[DataProvider('provideFromSecondsConvertsToWholeMicrosecondsCases')]
    public function testFromSecondsConvertsToWholeMicroseconds(float|int $seconds, int $expected): void
    {
        self::assertSame($expected, Microseconds::fromSeconds($seconds));
    }

    /**
     * @return iterable<string, array{float|int, int}>
     */
    public static function provideFromSecondsConvertsToWholeMicrosecondsCases(): iterable
    {
        yield 'whole int seconds' => [2, 2_000_000];

        yield 'fractional seconds' => [3.140592, 3_140_592];

        yield 'float noise rounds away' => [0.1, 100_000];

        yield 'half a microsecond rounds up' => [0.0000006, 1];

        yield 'sub-microsecond rounds to zero' => [0.0000004, 0];

        yield 'zero' => [0, 0];

        yield 'negative int clamps to zero' => [-1, 0];

        yield 'negative float clamps to zero' => [-0.5, 0];

        yield 'negative infinity clamps to zero' => [-\INF, 0];

        yield 'maximum seconds' => [Microseconds::MAX_SECONDS, 9_000_000_000_000_000_000];
    }

    #[DataProvider('provideInvalidDurationsAreRefusedCases')]
    public function testInvalidDurationsAreRefused(float $seconds, string $message): void
    {
        $this->expectException(InvalidDurationException::class);
        $this->expectExceptionMessageMatches('/'.preg_quote($message, '/').'/');

        Microseconds::fromSeconds($seconds);
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

    #[DataProvider('provideWholeSecondsAndRemainderPartitionMicrosecondsCases')]
    public function testWholeSecondsAndRemainderPartitionMicroseconds(int $microseconds, int $wholeSeconds, int $remainder): void
    {
        self::assertSame($wholeSeconds, Microseconds::wholeSeconds($microseconds));
        self::assertSame($remainder, Microseconds::remainder($microseconds));
    }

    /**
     * @return iterable<string, array{int, int, int}>
     */
    public static function provideWholeSecondsAndRemainderPartitionMicrosecondsCases(): iterable
    {
        yield 'zero' => [0, 0, 0];

        yield 'below one second' => [999_999, 0, 999_999];

        yield 'exactly one second' => [1_000_000, 1, 0];

        yield 'seconds with remainder' => [3_140_592, 3, 140_592];

        yield 'remainder near the next second' => [999_999_500, 999, 999_500];
    }
}
