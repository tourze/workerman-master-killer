# Workerman Master Killer

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/workerman-master-killer.svg?style=flat-square)](https://packagist.org/packages/tourze/workerman-master-killer)
[![PHP Version](https://img.shields.io/packagist/php-v/tourze/workerman-master-killer.svg?style=flat-square)](https://packagist.org/packages/tourze/workerman-master-killer)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/workerman-master-killer.svg?style=flat-square)](https://packagist.org/packages/tourze/workerman-master-killer)
[![License](https://img.shields.io/github/license/tourze/workerman-master-killer.svg?style=flat-square)](https://github.com/tourze/workerman-master-killer)

[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/workerman-master-killer/ci.yml?branch=master&style=flat-square)](https://github.com/tourze/workerman-master-killer/actions)
[![Coverage Status](https://img.shields.io/codecov/c/github/tourze/workerman-master-killer?style=flat-square)](https://codecov.io/gh/tourze/workerman-master-killer)

安全终止 Workerman 主进程的工具。灵感来源于 [Workerman 社区讨论](https://www.workerman.net/q/7958)。

## 功能特性

- 安全终止 Workerman 主进程，带超时保护机制
- 完整的日志支持，兼容 PSR-3 标准
- 使用 SIGQUIT 信号优雅关闭
- 可测试的设计，提供受保护的方法便于模拟
- 兼容 Workerman 5.1 及更高版本
- 适用于主进程无法正常退出的强制终止场景

## 安装

```bash
composer require tourze/workerman-master-killer
```

## 系统要求

- PHP 8.1 及以上版本
- ext-posix 扩展
- ext-pcntl 扩展
- Workerman 5.1 及以上版本
- PSR-3 兼容的日志实现

## 快速开始

```php
<?php

use Psr\Log\LoggerInterface;
use Tourze\Workerman\MasterKiller\MasterKiller;
use Workerman\Worker;

// 设置 Workerman PID 文件路径
Worker::$pidFile = '/path/to/workerman.pid';

// 创建 PSR-3 兼容的日志实例
$logger = new YourLoggerImplementation();

// 创建并使用终止器
$killer = new MasterKiller($logger);
$killer->killMaster(); // 此方法不会返回，会直接调用 exit
```

### 信号处理示例

```php
<?php

use Psr\Log\LoggerInterface;
use Tourze\Workerman\MasterKiller\MasterKiller;
use Workerman\Worker;

// 设置信号处理器实现优雅关闭
pcntl_signal(SIGTERM, function() use ($logger) {
    $killer = new MasterKiller($logger);
    $killer->killMaster();
});
```

## 工作原理

1. **读取 PID**：从 `Worker::$pidFile` 读取主进程 PID
2. **发送信号**：向主进程发送 SIGQUIT 信号
3. **等待监控**：最长等待 5 秒，等待进程退出
4. **记录结果**：日志记录操作结果（成功/失败）
5. **强制退出**：如果超时，强制退出程序

此过程使用轮询方式检查主进程是否仍然存活，每次检查间隔 10 毫秒。

## 高级用法

### 自定义超时配置

虽然默认超时时间为 5 秒，但您可以通过继承类来创建具有不同超时值的自定义实现：

```php
<?php

use Tourze\Workerman\MasterKiller\MasterKiller;

class CustomMasterKiller extends MasterKiller
{
    protected function waitForProcessStop(int $master_pid): never
    {
        $timeout = 10; // 自定义 10 秒超时
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

### 测试集成

该类专为便于测试而设计，提供可模拟的受保护方法：

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
    
    // 重写方法用于测试
    protected function exit(int $status = 0): never
    {
        throw new TestExitException($status);
    }
}
```

## API 文档

### MasterKiller 类

#### 构造函数

```php
public function __construct(LoggerInterface $logger)
```

使用提供的 PSR-3 日志记录器创建新的 MasterKiller 实例。

#### 方法

##### killMaster(): never

终止 Workerman 主进程。此方法不会返回，会直接调用 `exit()`。

**执行过程：**
- 从 `Worker::$pidFile` 读取主进程 PID
- 向主进程发送 SIGQUIT 信号
- 最长等待 5 秒等待进程退出
- 记录操作结果
- 成功时调用 `exit(0)`，超时时调用 `exit()`

## 贡献指南

请查看 [CONTRIBUTING.md](CONTRIBUTING.md) 了解详情。

### 开发

```bash
# 运行测试
./vendor/bin/phpunit

# 运行静态分析
./vendor/bin/phpstan analyse

# 检查代码风格
./vendor/bin/php-cs-fixer fix --dry-run
```

## 开源协议

MIT 开源协议。详情请查看 [License File](LICENSE)。

