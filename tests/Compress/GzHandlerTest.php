<?php

namespace Cesargb\Log\Test\Compress;

use Cesargb\Log\Compress\Gz;
use Cesargb\Log\Test\TestCase;
use Exception;

/**
 * v2 API compatibility: Gz::handler() must keep returning the compressed
 * filename as a string and throwing an Exception on failure, even though
 * Gz::process() now returns a ProcessResult.
 */
class GzHandlerTest extends TestCase
{
    public function test_handler_returns_compressed_filename(): void
    {
        $content = 'Lorem ipsum dolor sit amet.';

        file_put_contents(self::DIR_WORK.'file.log', $content);

        $filenameCompress = (new Gz)->handler(self::DIR_WORK.'file.log');

        $this->assertEquals(self::DIR_WORK.'file.log.gz', $filenameCompress);
        $this->assertFileDoesNotExist(self::DIR_WORK.'file.log');
        $this->assertEquals($content, implode('', (array) gzfile($filenameCompress)));
    }

    public function test_handler_throws_exception_when_source_file_not_exists(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(100);
        $this->expectExceptionMessageMatches('/not can read\.$/');

        set_error_handler(fn () => true);

        try {
            (new Gz)->handler(self::DIR_WORK.'file.log');
        } finally {
            restore_error_handler();
        }
    }

    public function test_handler_throws_exception_and_keeps_source_when_gz_target_is_not_writable(): void
    {
        file_put_contents(self::DIR_WORK.'file.log', 'content');

        mkdir(self::DIR_WORK.'file.log.gz');

        $this->expectException(Exception::class);
        $this->expectExceptionCode(101);
        $this->expectExceptionMessageMatches('/not can open\.$/');

        set_error_handler(fn () => true);

        try {
            (new Gz)->handler(self::DIR_WORK.'file.log');
        } finally {
            restore_error_handler();

            $this->assertFileExists(self::DIR_WORK.'file.log');

            rmdir(self::DIR_WORK.'file.log.gz');
        }
    }
}
