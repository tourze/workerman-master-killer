<?php

namespace Tourze\Workerman\MasterKiller\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\Workerman\MasterKiller\Exception\TestExitException;

class TestExitExceptionTest extends TestCase
{
    public function testConstructorWithExitCode(): void
    {
        $exitCode = 1;
        $exception = new TestExitException($exitCode);
        
        $this->assertStringContainsString("Exit called with code: $exitCode", $exception->getMessage());
    }
    
    public function testConstructorWithZeroExitCode(): void
    {
        $exitCode = 0;
        $exception = new TestExitException($exitCode);
        
        $this->assertStringContainsString("Exit called with code: $exitCode", $exception->getMessage());
    }
}