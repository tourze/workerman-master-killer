# Workerman Master Killer

Kill Workerman master process, inspired by [this Workerman forum post](https://www.workerman.net/q/7958).

## 安装

```bash
composer require tourze/workerman-master-killer
```

## 要求

- PHP 8.1 或更高版本
- ext-posix 扩展
- ext-pcntl 扩展
- Workerman 5.1 或更高版本
- PSR-3 日志实现

## 使用

```php
<?php

use Psr\Log\LoggerInterface;
use Tourze\Workerman\MasterKiller\MasterKiller;
use Workerman\Worker;

// 设置 PID 文件路径
Worker::$pidFile = '/path/to/workerman.pid';

// 创建日志记录器
$logger = new YourLoggerImplementation();

// 创建 MasterKiller 实例
$killer = new MasterKiller($logger);

// 杀死 Workerman 主进程
$killer->killMaster(); // 此方法不会返回，会调用 exit

// 可以在信号处理器或其他需要强制停止 Workerman 的地方使用
```

## 工作原理

`MasterKiller` 类提供了一个 `killMaster` 方法，它会：

1. 读取 `Worker::$pidFile` 获取主进程 PID
2. 向主进程发送 `SIGQUIT` 信号
3. 等待主进程退出，最长等待 5 秒
4. 记录结果并结束程序

这对于在某些 Workerman 进程无法正常退出的情况下强制停止非常有用。

## 协议

MIT License
