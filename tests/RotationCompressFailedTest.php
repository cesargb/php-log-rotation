<?php

namespace Cesargb\Log\Test;

use Cesargb\Log\Exceptions\RotationFailed;
use Cesargb\Log\Rotation;

class RotationCompressFailedTest extends TestCase
{
    private function makeCompressTargetUnwritable(): void
    {
        // RotativeProcessor will try to unlink() this before renaming file.log
        // to file.log.1; unlink() silently fails on a directory, so the path
        // stays occupied and gzopen() fails once Gz tries to compress into it.
        mkdir(self::DIR_WORK.'file.log.1.gz');
    }

    public function test_throws_exception_with_gz_error_message(): void
    {
        file_put_contents(self::DIR_WORK.'file.log', microtime(true));

        $this->makeCompressTargetUnwritable();

        $this->expectException(RotationFailed::class);
        $this->expectExceptionMessageMatches('/file\.log\.1\.gz not can open\.$/');
        $this->expectExceptionCode(101);

        $rotation = new Rotation;

        set_error_handler(fn () => true);

        try {
            $rotation->compress()->files(1)->rotate(self::DIR_WORK.'file.log');
        } finally {
            restore_error_handler();

            rmdir(self::DIR_WORK.'file.log.1.gz');
        }
    }

    public function test_catch_receives_gz_error_message_and_keeps_uncompressed_file(): void
    {
        file_put_contents(self::DIR_WORK.'file.log', microtime(true));

        $this->makeCompressTargetUnwritable();

        $rotation = new Rotation;

        $caught = null;

        set_error_handler(fn () => true);

        try {
            $rotation
                ->compress()
                ->files(1)
                ->catch(function (RotationFailed $exception) use (&$caught) {
                    $caught = $exception;
                })
                ->rotate(self::DIR_WORK.'file.log');
        } finally {
            restore_error_handler();

            rmdir(self::DIR_WORK.'file.log.1.gz');
        }

        $this->assertInstanceOf(RotationFailed::class, $caught);
        $this->assertStringEndsWith('file.log.1.gz not can open.', $caught->getMessage());

        $this->assertFileExists(self::DIR_WORK.'file.log.1');
    }
}
