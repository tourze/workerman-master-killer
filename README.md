# Workerman Master Killer

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/workerman-master-killer.svg?style=flat-square)](https://packagist.org/packages/tourze/workerman-master-killer)
[![PHP Version](https://img.shields.io/packagist/php-v/tourze/workerman-master-killer.svg?style=flat-square)](https://packagist.org/packages/tourze/workerman-master-killer)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/workerman-master-killer.svg?style=flat-square)](https://packagist.org/packages/tourze/workerman-master-killer)
[![License](https://img.shields.io/github/license/tourze/workerman-master-killer.svg?style=flat-square)](https://github.com/tourze/workerman-master-killer)

[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/workerman-master-killer/ci.yml?branch=master&style=flat-square)](https://github.com/tourze/workerman-master-killer/actions)
[![Coverage Status](https://img.shields.io/codecov/c/github/tourze/workerman-master-killer?style=flat-square)](https://codecov.io/gh/tourze/workerman-master-killer)

A utility to safely kill the Workerman master process. Inspired by 
[this Workerman forum post](https://www.workerman.net/q/7958).

## Features

- Safe termination of Workerman master process with timeout protection
- Comprehensive logging support with PSR-3 compatible logger
- Graceful shutdown using SIGQUIT signal
- Testable design with protected methods for mocking
- Compatible with Workerman 5.1+
- Useful for force-stopping master process when it cannot exit normally

## Installation

```bash
composer require tourze/workerman-master-killer
```

## Requirements

- PHP 8.1 or higher
- ext-posix extension
- ext-pcntl extension
- Workerman 5.1 or higher
- PSR-3 compatible logger

## Quick Start

```php
<?php

use Psr\Log\LoggerInterface;
use Tourze\Workerman\MasterKiller\MasterKiller;
use Workerman\Worker;

// Set up your Workerman PID file
Worker::$pidFile = '/path/to/workerman.pid';

// Create a PSR-3 compatible logger
$logger = new YourLoggerImplementation();

// Create and use the killer
$killer = new MasterKiller($logger);
$killer->killMaster(); // This method never returns, will call exit
```

### Signal Handler Example

```php
<?php

use Psr\Log\LoggerInterface;
use Tourze\Workerman\MasterKiller\MasterKiller;
use Workerman\Worker;

// Set up signal handler for graceful shutdown
pcntl_signal(SIGTERM, function() use ($logger) {
    $killer = new MasterKiller($logger);
    $killer->killMaster();
});
```

## How It Works

1. **Read PID**: Reads the master process PID from `Worker::$pidFile`
2. **Send Signal**: Sends SIGQUIT signal to the master process
3. **Wait & Monitor**: Waits up to 5 seconds for the process to exit
4. **Log Results**: Logs the operation result (success/failure)
5. **Force Exit**: If timeout occurs, forcibly exits the program

The process uses polling to check if the master process is still alive, with a 10ms sleep between checks.

## Advanced Usage

### Custom Timeout Configuration

While the default timeout is 5 seconds, you can create a custom implementation 
with different timeout values by extending the class:

```php
<?php

use Tourze\Workerman\MasterKiller\MasterKiller;

class CustomMasterKiller extends MasterKiller
{
    protected function waitForProcessStop(int $master_pid): never
    {
        $timeout = 10; // Custom 10-second timeout
        $start_time = $this->time();
        
        while (true) {
            if ($this->isMasterProcessAlive($master_pid)) {
                $this->handleProcessStillAlive($start_time, $timeout);
                continue;
            }
            
            $this->handleProcessStopped();
        }
    }
}
```

### Testing Integration

The class is designed for easy testing with protected methods that can be mocked:

```php
<?php

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Tourze\Workerman\MasterKiller\MasterKiller;

class TestableMasterKiller extends MasterKiller
{
    public function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);
    }
    
    // Override methods for testing
    protected function exit(int $status = 0): never
    {
        throw new TestExitException($status);
    }
}
```

## API Documentation

### MasterKiller Class

#### Constructor

```php
public function __construct(LoggerInterface $logger)
```

Creates a new MasterKiller instance with the provided PSR-3 logger.

#### Methods

##### killMaster(): never

Kills the Workerman master process. This method never returns and will call `exit()`.

**Process:**
- Reads master PID from `Worker::$pidFile`
- Sends SIGQUIT signal to master process
- Waits up to 5 seconds for process to exit
- Logs operation result
- Calls `exit(0)` on success or `exit()` on timeout

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

### Development

```bash
# Run tests
./vendor/bin/phpunit

# Run static analysis
./vendor/bin/phpstan analyse

# Check code style
./vendor/bin/php-cs-fixer fix --dry-run
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

