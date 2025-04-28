# Workerman Master Killer

[![Packagist Version](https://img.shields.io/packagist/v/tourze/workerman-master-killer)](https://packagist.org/packages/tourze/workerman-master-killer)
[![PHP Version](https://img.shields.io/packagist/php-v/tourze/workerman-master-killer)](https://packagist.org/packages/tourze/workerman-master-killer)
[![License](https://img.shields.io/github/license/tourze/workerman-master-killer)](https://github.com/tourze/workerman-master-killer)

A utility to safely kill the Workerman master process. Inspired by [this Workerman forum post](https://www.workerman.net/q/7958).

---

## Features

- One-command safe termination of Workerman master process
- Supports logging for troubleshooting
- Compatible with Workerman 5.1+
- Useful for force-stopping master process when it cannot exit normally

## Requirements

- PHP 8.1 or higher
- ext-posix extension
- ext-pcntl extension
- Workerman 5.1 or higher
- PSR-3 compatible logger

## Installation

Install via Composer:

```bash
composer require tourze/workerman-master-killer
```

## Quick Start

```php
use Psr\Log\LoggerInterface;
use Tourze\Workerman\MasterKiller\MasterKiller;
use Workerman\Worker;

Worker::$pidFile = '/path/to/workerman.pid';
$logger = new YourLoggerImplementation();
$killer = new MasterKiller($logger);
$killer->killMaster(); // This method never returns, will call exit
```

You can use this in a signal handler or anywhere you need to force stop Workerman.

## How It Works

- Reads the master process PID from `Worker::$pidFile`
- Sends SIGQUIT signal to the master process
- Waits up to 5 seconds for the process to exit
- Logs the result
- If timeout, forcibly exits the program

## Documentation

- See source code comments for API details
- Custom logger implementations supported (must implement PSR-3)

## Contributing

- Issues and PRs are welcome
- Please follow PSR code style
- Tests should cover main features

## License

MIT License

## Changelog

See [CHANGELOG.md] if available.
