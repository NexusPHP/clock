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
 * A delay that blocks the current thread.
 */
final readonly class NativeDelay implements Delay
{
    #[\Override]
    public function sleep(float|int $seconds): void
    {
        $microseconds = Microseconds::fromSeconds($seconds);

        sleep(Microseconds::wholeSeconds($microseconds));
        usleep(Microseconds::remainder($microseconds));
    }
}
