# Nexus Clock

[![PHP](http://poser.pugx.org/nexusphp/clock/require/php)](https://packagist.org/packages/nexusphp/clock)
[![Latest Stable Version](http://poser.pugx.org/nexusphp/clock/v)](https://packagist.org/packages/nexusphp/clock)
[![Unit Tests](https://github.com/NexusPHP/clock/actions/workflows/unit-tests.yml/badge.svg)](https://github.com/NexusPHP/clock/actions/workflows/unit-tests.yml)
[![Code Style](https://github.com/NexusPHP/clock/actions/workflows/code-style.yml/badge.svg)](https://github.com/NexusPHP/clock/actions/workflows/code-style.yml)
[![Static Analysis](https://github.com/NexusPHP/clock/actions/workflows/static-analysis.yml/badge.svg)](https://github.com/NexusPHP/clock/actions/workflows/static-analysis.yml)
[![Mutation Tests](https://github.com/NexusPHP/clock/actions/workflows/mutation.yml/badge.svg)](https://github.com/NexusPHP/clock/actions/workflows/mutation.yml)
[![Coverage Status](https://coveralls.io/repos/github/NexusPHP/clock/badge.svg?branch=1.x)](https://coveralls.io/github/NexusPHP/clock?branch=1.x)
[![license MIT](https://img.shields.io/github/license/nexusphp/clock)](LICENSE)
[![Total Downloads](https://poser.pugx.org/nexusphp/clock/downloads)](//packagist.org/packages/nexusphp/clock)

Nexus Clock decouples applications from calendar time, monotonic time, and waiting for better testing.

## Requirements

- PHP 8.3+
- Composer

## Installation

```
composer require nexusphp/clock
```

## Usage

### Reading calendar time

`Clock` is [PSR-20]'s `ClockInterface` and nothing more. Use it wherever code needs to know
what time it is in the world: timestamps, due dates, expiry checks.

```php
use Nexus\Clock\Clock;
use Nexus\Clock\SystemClock;

final readonly class AuditTrail
{
    public function __construct(
        private Clock $clock = new SystemClock(),
    ) {}

    public function record(string $event): string
    {
        return \sprintf('[%s] %s', $this->clock->now()->format(\DATE_ATOM), $event);
    }
}
```

`SystemClock` defaults to UTC, the only value a library should ever pick. Pass a
`\DateTimeZone` or a timezone name when the application knows better.

[PSR-20]: https://www.php-fig.org/psr/psr-20/

### Measuring elapsed time

`Stopwatch` answers how long since some earlier moment. Wall time is the wrong instrument for
durations, since NTP steps, manual adjustment, and suspend/resume all corrupt a difference of
two calendar readings. `HighResolutionStopwatch` reads the system's monotonic timer instead.

```php
use Nexus\Clock\HighResolutionStopwatch;

$stopwatch = new HighResolutionStopwatch();

$start = $stopwatch->read();
doWork();
$elapsedSeconds = $stopwatch->read() - $start;
```

### Waiting

`Delay` is a scheduling act, not a time reading. `NativeDelay` blocks the current thread with
`usleep()`. Asynchronous consumers implement `Delay` once over their own event loop (a Revolt
timer, a ReactPHP timer) and keep the same seam. Zero or negative seconds are a no-op, and a
duration that is not a finite representable number of seconds throws `InvalidDurationException`.

```php
use Nexus\Clock\NativeDelay;

$delay = new NativeDelay();
$delay->sleep(0.5);
```

### Testing with FrozenClock

`FrozenClock` implements all three interfaces behind one advanceable state. When code sleeps
two seconds, the frozen `now()` and the frozen `read()` both advance by two seconds, keeping
the test scenario coherent. Its default epoch is deterministic, so two instances built in one
test agree with each other.

```php
use Nexus\Clock\FrozenClock;

$clock = new FrozenClock('2026-08-25 12:00:00+00:00');

$clock->advance(30);

$clock->now();  // 2026-08-25 12:00:30, exactly
$clock->read(); // 30.0
```

`advance()` and `sleep()` behave identically. A test freezing a deadline advances a clock, a
test pacing a retry loop observes the sleep.

## Contributing

Contributions are very much welcome. If you see an improvement or bugfix, open a
[PR](https://github.com/NexusPHP/clock/pulls) now!

## License

Released under the [MIT License](LICENSE).
