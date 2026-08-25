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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(HighResolutionStopwatch::class)]
final class HighResolutionStopwatchTest extends TestCase
{
    public function testReadIsPositiveAndMonotonic(): void
    {
        $stopwatch = new HighResolutionStopwatch();

        $first = $stopwatch->read();
        $second = $stopwatch->read();

        self::assertGreaterThan(0.0, $first);
        self::assertGreaterThanOrEqual($first, $second);
    }

    public function testReadMeasuresElapsedTimeInSeconds(): void
    {
        $stopwatch = new HighResolutionStopwatch();

        $start = $stopwatch->read();
        usleep(20_000);
        $elapsed = $stopwatch->read() - $start;

        self::assertGreaterThanOrEqual(0.02, $elapsed);
        self::assertLessThan(0.5, $elapsed);
    }
}
