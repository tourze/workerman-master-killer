<?php

namespace Tourze\Workerman\MasterKiller\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\Workerman\MasterKiller\Exception\TestExitException;

/**
 * @internal
 */
#[CoversClass(TestExitException::class)]
final class TestExitExceptionTest extends AbstractExceptionTestCase
{
    public function testConstructorWithExitCode(): void
    {
        $exitCode = 1;
        $exception = new TestExitException($exitCode);

        $this->assertStringContainsString("Exit called with code: {$exitCode}", $exception->getMessage());
    }

    public function testConstructorWithZeroExitCode(): void
    {
        $exitCode = 0;
        $exception = new TestExitException($exitCode);

        $this->assertStringContainsString("Exit called with code: {$exitCode}", $exception->getMessage());
    }
}
