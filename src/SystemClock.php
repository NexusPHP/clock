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
 * A clock that relies on the system time.
 *
 * @immutable
 */
final readonly class SystemClock implements Clock
{
    private \DateTimeZone $timezone;

    public function __construct(\DateTimeZone|string $timezone = 'UTC')
    {
        $this->timezone = \is_string($timezone) ? new \DateTimeZone($timezone) : $timezone;
    }

    #[\Override]
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('now', $this->timezone);
    }
}
