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
 * A wait for a duration of seconds.
 */
interface Delay
{
    /**
     * Waits for the given number of seconds. A zero or negative duration is a no-op.
     *
     * @throws InvalidDurationException
     */
    public function sleep(float|int $seconds): void;
}
