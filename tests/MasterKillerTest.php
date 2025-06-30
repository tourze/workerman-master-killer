<?php

namespace Tourze\Workerman\MasterKiller\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Tourze\Workerman\MasterKiller\Exception\TestExitException;
use Workerman\Worker;

class MasterKillerTest extends TestCase
{
    private ?string $originalPidFile = null;

    /**
     * 备份和设置测试环境
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->originalPidFile = Worker::$pidFile;
        Worker::$pidFile = '/tmp/workerman.pid';
    }

    /**
     * 恢复测试环境
     */
    protected function tearDown(): void
    {
        Worker::$pidFile = $this->originalPidFile;
        parent::tearDown();
    }

    /**
     * 测试杀死主进程成功的情况
     */
    public function testKillMaster_WhenProcessExitsSuccessfully()
    {
        // 创建 Logger Mock
        $logger = $this->createMock(LoggerInterface::class);

        // 设置预期的日志调用
        $logger->expects($this->atLeastOnce())
            ->method('warning');

        $logger->expects($this->once())
            ->method('info')
            ->with('Workerman stop success');

        // 创建测试代理类
        $killer = new MasterKillerTestDouble($logger);

        // 设置 posix_kill 返回值，第一次返回 true，第二次返回 false（表示进程已退出）
        $killer->posixKillReturns = [true, false];

        // 执行测试方法并捕获预期异常
        try {
            $killer->killMasterWithoutExit();
            $this->fail('Expected TestExitException was not thrown');
        } catch (TestExitException $e) {
            $this->assertStringContainsString('Exit called with code: 0', $e->getMessage());
        }

        // 断言 exit 被调用且返回码为 0
        $this->assertTrue($killer->exitCalled);
        $this->assertEquals(0, $killer->exitCode);

        // 断言 posix_kill 被调用两次
        $this->assertCount(2, $killer->posixKillCalls);

        // 断言第一次调用发送 SIGQUIT 信号
        $this->assertEquals(SIGQUIT, $killer->posixKillCalls[0]['signal']);
        $this->assertEquals(12345, $killer->posixKillCalls[0]['pid']);

        // 断言第二次调用检查进程是否存活
        $this->assertEquals(0, $killer->posixKillCalls[1]['signal']);
        $this->assertEquals(12345, $killer->posixKillCalls[1]['pid']);
    }

    /**
     * 测试杀死主进程超时的情况
     */
    public function testKillMaster_WhenProcessTimeout()
    {
        // 创建 Logger Mock
        $logger = $this->createMock(LoggerInterface::class);

        // 设置预期的日志调用
        $logger->expects($this->atLeastOnce())
            ->method('warning');

        // 创建测试代理类  
        $killer = new MasterKillerTestDouble($logger);

        // 设置 posix_kill 始终返回 true（表示进程仍然活着）
        $killer->posixKillReturns = array_fill(0, 10, true);

        // 设置时间增量，使其快速超时
        $killer->timeIncrement = 2;

        // 执行测试方法并捕获预期异常
        try {
            $killer->killMasterWithoutExit();
            $this->fail('Expected TestExitException was not thrown');
        } catch (TestExitException $e) {
            $this->assertStringContainsString('Exit called with code: 0', $e->getMessage());
        }

        // 断言 exit 被调用
        $this->assertTrue($killer->exitCalled);

        // 断言 posix_kill 至少被调用一次以上
        $this->assertGreaterThanOrEqual(2, count($killer->posixKillCalls));
    }

    /**
     * 测试 PID 文件不存在的情况
     */
    public function testKillMaster_WhenPidFileNotExists()
    {
        // 创建 Logger Mock
        $logger = $this->createMock(LoggerInterface::class);

        // 设置预期的日志调用
        $logger->expects($this->once())
            ->method('warning')
            ->with('Workerman[0] stop fail');

        $logger->expects($this->once())
            ->method('info')
            ->with('Workerman stop success');

        // 创建测试代理类
        $killer = new MasterKillerTestDouble($logger);

        // 设置 PID 文件不存在
        $killer->isFileReturn = false;

        // 设置 posix_kill 在检查存活时返回 false
        $killer->posixKillReturns = [false];

        // 执行测试方法并捕获预期异常
        try {
            $killer->killMasterWithoutExit();
            $this->fail('Expected TestExitException was not thrown');
        } catch (TestExitException $e) {
            $this->assertStringContainsString('Exit called with code: 0', $e->getMessage());
        }

        // 断言 exit 被调用
        $this->assertTrue($killer->exitCalled);

        // 当 PID 为 0 时，posix_kill 可能不会被调用，但我们仍需检查
        // 如果有调用，那么应该使用正确的参数
        if (!empty($killer->posixKillCalls)) {
            $this->assertEquals(0, $killer->posixKillCalls[0]['signal']);
        }
    }
}
