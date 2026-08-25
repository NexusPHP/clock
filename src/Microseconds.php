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
 * A converter of durations in seconds to counts of whole microseconds.
 *
 * @internal
 */
final class Microseconds
{
    public const int PER_SECOND = 1_000_000;
    public const int MAX = 9_000_000_000_000_000_000;
    public const float MAX_SECONDS = self::MAX / self::PER_SECOND;

    /**
     * Converts a duration in seconds to whole microseconds, clamping non-positive durations to zero.
     *
     * @throws InvalidDurationException
     */
    public static function fromSeconds(float|int $seconds): int
    {
        if (is_nan($seconds) || $seconds > self::MAX_SECONDS) {
            throw new InvalidDurationException(\sprintf('Invalid duration of %s seconds.', var_export($seconds, true)));
        }

        return (int) round(max(0, $seconds) * self::PER_SECOND);
    }

    public static function wholeSeconds(int $microseconds): int
    {
        return intdiv($microseconds, self::PER_SECOND);
    }

    /**
     * The microseconds left over after the whole seconds are removed.
     */
    public static function remainder(int $microseconds): int
    {
        return $microseconds % self::PER_SECOND;
    }
}
