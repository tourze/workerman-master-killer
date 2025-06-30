<?php

namespace Tourze\Workerman\MasterKiller\Exception;

use RuntimeException;

/**
 * 用于测试场景中模拟 exit 调用的异常
 */
class TestExitException extends RuntimeException
{
    public function __construct(int $exitCode)
    {
        parent::__construct("Exit called with code: $exitCode");
    }
}