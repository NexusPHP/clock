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
 * A monotonic reading of elapsed time.
 */
interface Stopwatch
{
    /**
     * Reads the seconds elapsed since an arbitrary fixed origin, monotonic and unaffected by wall-clock changes.
     */
    public function read(): float;
}
