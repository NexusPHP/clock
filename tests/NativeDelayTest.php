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

use Nexus\Clock\HighResolutionStopwatch;
use Nexus\Clock\InvalidDurationException;
use Nexus\Clock\NativeDelay;
use Nexus\PHPUnit\Tachycardia\Attribute\TimeLimit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(NativeDelay::class)]
final class NativeDelayTest extends TestCase
{
    #[TimeLimit(2.50)]
    public function testSleepBlocksForWholeAndFractionalSeconds(): void
    {
        $stopwatch = new HighResolutionStopwatch();

        $start = $stopwatch->read();
        (new NativeDelay())->sleep(1.55);
        $elapsed = $stopwatch->read() - $start;

        self::assertGreaterThanOrEqual(1.55, $elapsed);
        self::assertLessThan(2.0, $elapsed);
    }

    public function testSleepBlocksForSubSecondDurations(): void
    {
        $stopwatch = new HighResolutionStopwatch();

        $start = $stopwatch->read();
        (new NativeDelay())->sleep(0.08);
        $elapsed = $stopwatch->read() - $start;

        self::assertGreaterThanOrEqual(0.08, $elapsed);
        self::assertLessThan(0.5, $elapsed);
    }

    #[DataProvider('provideSleepReturnsImmediatelyOnNonPositiveSecondsCases')]
    public function testSleepReturnsImmediatelyOnNonPositiveSeconds(float|int $seconds): void
    {
        $stopwatch = new HighResolutionStopwatch();

        $start = $stopwatch->read();
        (new NativeDelay())->sleep($seconds);
        $elapsed = $stopwatch->read() - $start;

        self::assertLessThan(0.05, $elapsed);
    }

    /**
     * @return iterable<string, array{float|int}>
     */
    public static function provideSleepReturnsImmediatelyOnNonPositiveSecondsCases(): iterable
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

        (new NativeDelay())->sleep($seconds);
    }

    /**
     * @return iterable<string, array{float, string}>
     */
    public static function provideInvalidDurationsAreRefusedCases(): iterable
    {
        yield 'not a number' => [\NAN, 'Invalid duration of NAN seconds.'];

        yield 'positive infinity' => [\INF, 'Invalid duration of INF seconds.'];

        yield 'beyond the microsecond range' => [10_000_000_000_000, 'Invalid duration of 10000000000000.0 seconds.'];

        yield 'just past the bound' => [9_000_000_000_001.0, 'Invalid duration of 9000000000001.0 seconds.'];
    }
}
