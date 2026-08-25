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

use Psr\Clock\ClockInterface;

/**
 * A wall clock reading calendar time.
 */
interface Clock extends ClockInterface
{
}
