<?php

namespace Tourze\Workerman\MasterKiller\Tests;

use RuntimeException;
use Tourze\Workerman\MasterKiller\Exception\TestExitException;
use Tourze\Workerman\MasterKiller\MasterKiller;

/**
 * 测试代理类，用于控制 MasterKiller 的行为以进行单元测试
 */
class MasterKillerTestDouble extends MasterKiller
{
    /**
     * 模拟文件是否存在的返回值
     *
     * @var bool
     */
    public $isFileReturn = true;

    /**
     * 模拟文件内容
     *
     * @var string
     */
    public $fileContents = '12345';

    /**
     * 记录 posix_kill 调用情况
     *
     * @var array
     */
    public $posixKillCalls = [];

    /**
     * posix_kill 函数返回值队列
     *
     * @var array
     */
    public $posixKillReturns = [];

    /**
     * 当前模拟时间
     *
     * @var int
     */
    public $currentTime = 1000;

    /**
     * 时间增量
     *
     * @var int
     */
    public $timeIncrement = 1;

    /**
     * 是否已调用 exit
     *
     * @var bool
     */
    public $exitCalled = false;

    /**
     * exit 调用的状态码
     *
     * @var int|null
     */
    public $exitCode = null;

    /**
     * 重写 is_file 函数
     */
    protected function isFile(string $filename): bool
    {
        return $this->isFileReturn;
    }

    /**
     * 重写 file_get_contents 函数
     */
    protected function fileGetContents(string $filename): string
    {
        return $this->fileContents;
    }

    /**
     * 重写 posix_kill 函数
     */
    protected function posixKill(int $pid, int $signal): bool
    {
        $this->posixKillCalls[] = [
            'pid' => $pid,
            'signal' => $signal,
        ];

        if (!empty($this->posixKillReturns)) {
            return array_shift($this->posixKillReturns);
        }

        return true;
    }

    /**
     * 重写 time 函数
     */
    protected function time(): int
    {
        $time = $this->currentTime;
        $this->currentTime += $this->timeIncrement;
        return $time;
    }

    /**
     * 重写 usleep 函数，避免真正的延迟
     */
    protected function usleep(int $microseconds): void
    {
        // 不执行任何操作
    }

    /**
     * 重写 exit 函数以避免真正退出测试
     */
    protected function exit(int $status = 0): never
    {
        $this->exitCalled = true;
        $this->exitCode = $status;
        throw new TestExitException($status);
    }

    /**
     * 封装原始的 killMaster 方法，捕获 exit 调用产生的异常
     *
     * 在测试中使用此方法代替 killMaster
     */
    public function killMasterWithoutExit(): void
    {
        try {
            $this->killMaster();
        } catch (TestExitException $e) {
            // 不捕获异常，让它传播到测试方法
            throw $e;
        }
    }
}
