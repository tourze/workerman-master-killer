# Workerman Master Killer

杀死 Workerman 主进程的工具，灵感来源于 [Workerman 社区讨论](https://www.workerman.net/q/7958)。

---

![Packagist Version](https://img.shields.io/packagist/v/tourze/workerman-master-killer)
![PHP from Packagist](https://img.shields.io/packagist/php-v/tourze/workerman-master-killer)
![License](https://img.shields.io/github/license/tourze/workerman-master-killer)

## 功能特性

- 一键安全终止 Workerman 主进程
- 支持日志记录，便于排查和追踪
- 兼容 Workerman 5.1 及更高版本
- 适用于主进程无法正常退出的场景

## 安装说明

- 需要 PHP 8.1 及以上版本
- 依赖 ext-posix、ext-pcntl 扩展
- 依赖 Workerman 5.1 及以上
- 需要 PSR-3 日志实现

通过 Composer 安装：

```bash
composer require tourze/workerman-master-killer
```

## 快速开始

```php
use Psr\Log\LoggerInterface;
use Tourze\Workerman\MasterKiller\MasterKiller;
use Workerman\Worker;

Worker::$pidFile = '/path/to/workerman.pid';
$logger = new YourLoggerImplementation();
$killer = new MasterKiller($logger);
$killer->killMaster(); // 此方法不会返回，会直接 exit
```

可在信号处理器或需要强制停止 Workerman 的场景下调用。

## 工作原理

- 读取 `Worker::$pidFile` 获取主进程 PID
- 向主进程发送 SIGQUIT 信号
- 最长等待 5 秒，轮询检测主进程是否退出
- 日志记录操作结果
- 超时未退出则强制退出程序

## 详细文档

- API 详见源码注释
- 支持自定义日志实现

## 贡献指南

- 欢迎提交 Issue 与 PR
- 遵循 PSR 代码风格
- 测试用例请覆盖主要功能

## 版权和许可

- 开源协议：MIT License
- 作者：tourze 团队

## 更新日志

- 详见 [CHANGELOG.md]（如有）
