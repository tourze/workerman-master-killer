<?php

namespace Tourze\Workerman\MasterKiller\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\Workerman\MasterKiller\Exception\FileReadException;

/**
 * @internal
 */
#[CoversClass(FileReadException::class)]
final class FileReadExceptionTest extends AbstractExceptionTestCase
{
    public function testConstructorWithMessage(): void
    {
        $message = 'Failed to read file: /path/to/file.txt';
        $exception = new FileReadException($message);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }

    public function testConstructorWithMessageAndCode(): void
    {
        $message = 'File not readable';
        $code = 404;
        $exception = new FileReadException($message, $code);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
    }
}
