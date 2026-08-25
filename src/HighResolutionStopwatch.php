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

namespace Nexus\Clock;

/**
 * A stopwatch backed by the system's high-resolution monotonic timer.
 */
final class HighResolutionStopwatch implements Stopwatch
{
    private const int NANOSECONDS_PER_SECOND = 1_000_000_000;

    #[\Override]
    public function read(): float
    {
        return hrtime(true) / self::NANOSECONDS_PER_SECOND;
    }
}
