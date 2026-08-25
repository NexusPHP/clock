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
 * A test double freezing all three time capabilities behind one advanceable state.
 */
final class FrozenClock implements Clock, Delay, Stopwatch
{
    private \DateTimeImmutable $now;
    private int $advancedMicroseconds = 0;

    /**
     * A string $now without timezone information is interpreted as UTC.
     */
    public function __construct(\DateTimeImmutable|string $now = '2020-01-01T00:00:00+00:00')
    {
        $this->now = \is_string($now) ? new \DateTimeImmutable($now, new \DateTimeZone('UTC')) : $now;
    }

    #[\Override]
    public function now(): \DateTimeImmutable
    {
        return $this->now;
    }

    #[\Override]
    public function read(): float
    {
        return $this->advancedMicroseconds / Microseconds::PER_SECOND;
    }

    #[\Override]
    public function sleep(float|int $seconds): void
    {
        $microseconds = Microseconds::fromSeconds($seconds);

        if ($microseconds < 1) {
            return;
        }

        if ($microseconds > Microseconds::MAX - $this->advancedMicroseconds) {
            throw new InvalidDurationException(\sprintf('Cannot advance the clock by %s more seconds.', var_export($seconds, true)));
        }

        $this->now = $this->now->modify(\sprintf(
            '%+d seconds %+d microseconds',
            Microseconds::wholeSeconds($microseconds),
            Microseconds::remainder($microseconds),
        ));
        $this->advancedMicroseconds += $microseconds;
    }

    /**
     * Behaves identically to `sleep()`.
     *
     * @throws InvalidDurationException
     */
    public function advance(float|int $seconds): void
    {
        $this->sleep($seconds);
    }
}
