<?php

namespace Tourze\Workerman\MasterKiller;

use Psr\Log\LoggerInterface;
use Workerman\Worker;

class MasterKiller
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    /**
     * 杀死 Workerman 主进程
     * 
     * 该方法不会返回，会直接调用 exit 退出
     */
    public function killMaster(): never
    {
        $master_pid = $this->isFile(Worker::$pidFile) ? (int)$this->fileGetContents(Worker::$pidFile) : 0;
        $sig = \SIGQUIT;
        // Send stop signal to master process.
        $master_pid && $this->posixKill($master_pid, $sig);
        $this->logger->warning("Workerman[$master_pid] stop fail");
        // Timeout.
        $timeout = 5;
        $start_time = $this->time();
        // Check master process is still alive?
        while (1) {
            $master_is_alive = $master_pid && $this->posixKill((int)$master_pid, 0);
            if ($master_is_alive) {
                // Timeout?
                if ($this->time() - $start_time >= $timeout) {
                    $this->logger->warning("Workerman stop fail");
                    $this->exit();
                }
                // Waiting amoment.
                $this->usleep(10000);
                continue;
            }
            // Stop success.
            $this->logger->info("Workerman stop success");
            $this->exit(0);
        }
    }

    /**
     * 检查文件是否存在
     */
    protected function isFile(string $filename): bool
    {
        return \is_file($filename);
    }

    /**
     * 获取文件内容
     */
    protected function fileGetContents(string $filename): string
    {
        return \file_get_contents($filename);
    }

    /**
     * 向进程发送信号
     */
    protected function posixKill(int $pid, int $signal): bool
    {
        return \posix_kill($pid, $signal);
    }

    /**
     * 获取当前时间
     */
    protected function time(): int
    {
        return \time();
    }

    /**
     * 微秒级休眠
     */
    protected function usleep(int $microseconds): void
    {
        \usleep($microseconds);
    }

    /**
     * 退出程序
     * 
     * 该方法不会返回
     */
    protected function exit(int $status = 0): never
    {
        exit($status);
    }
}
