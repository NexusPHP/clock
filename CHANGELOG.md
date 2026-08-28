# Changelog

All notable changes to this library will be documented in this file:

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [v1.0.0](https://github.com/NexusPHP/clock/releases/tag/v1.0.0) - 2026-08-28

### Added

- `Clock` interface extending [PSR-20](https://www.php-fig.org/psr/psr-20/)'s `ClockInterface`, with `SystemClock` reading the system time in UTC by default
- `Stopwatch` interface for monotonic elapsed-time readings, with `HighResolutionStopwatch` backed by the system's high-resolution timer
- `Delay` interface for waiting, with `NativeDelay` blocking the current thread
- `FrozenClock` test double implementing all three interfaces behind one advanceable state
- `InvalidDurationException` thrown for durations that are not a finite representable number of seconds
