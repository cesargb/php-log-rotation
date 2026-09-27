<?php

namespace Cesargb\Log\Test\Compress;

use Cesargb\Log\Compress\Gz;
use Cesargb\Log\Test\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class GzHandlerTest extends TestCase
{
    public function test_handler_returns_successful_result(): void
    {
        $content = 'Lorem ipsum dolor sit amet.';

        file_put_contents(self::DIR_WORK.'file.log', $content);

        $result = (new Gz)->handler(self::DIR_WORK.'file.log');

        $this->assertTrue($result->isSuccessful());
        $this->assertEquals(self::DIR_WORK.'file.log', $result->filenameSource);
        $this->assertEquals(self::DIR_WORK.'file.log.gz', $result->filenameTarget);
        $this->assertNull($result->error);

        $this->assertFileDoesNotExist(self::DIR_WORK.'file.log');
        $this->assertEquals($content, implode('', (array) gzfile(self::DIR_WORK.'file.log.gz')));
    }

    /**
     * @return int[][]
     */
    public static function chunkBoundarySizeProvider(): array
    {
        return [
            'one chunk exactly' => [1024 * 512],
            'two chunks exactly' => [2 * 1024 * 512],
        ];
    }

    /**
     * Regression test: gzwrite() returns 0 (not false) for an empty string,
     * which a loose `== false` comparison used to treat as a failure.
     * A file whose size is an exact multiple of the read chunk (512 KiB)
     * makes the last fread() call return ''.
     */
    #[DataProvider('chunkBoundarySizeProvider')]
    public function test_handler_succeeds_when_file_size_is_exact_multiple_of_chunk_size(int $size): void
    {
        file_put_contents(self::DIR_WORK.'file.log', str_repeat('a', $size));

        $result = (new Gz)->handler(self::DIR_WORK.'file.log');

        $this->assertTrue($result->isSuccessful());
        $this->assertEquals(
            str_repeat('a', $size),
            implode('', (array) gzfile(self::DIR_WORK.'file.log.gz'))
        );
    }

    public function test_handler_returns_failed_result_when_source_file_not_exists(): void
    {
        set_error_handler(fn () => true);

        try {
            $result = (new Gz)->handler(self::DIR_WORK.'file.log');
        } finally {
            restore_error_handler();
        }

        $this->assertFalse($result->isSuccessful());
        $this->assertNull($result->filenameTarget);
        $this->assertStringContainsString('not can read', (string) $result->error);
    }

    public function test_handler_returns_failed_result_and_keeps_source_when_gz_target_is_not_writable(): void
    {
        file_put_contents(self::DIR_WORK.'file.log', 'content');

        mkdir(self::DIR_WORK.'file.log.gz');

        set_error_handler(fn () => true);

        try {
            $result = (new Gz)->handler(self::DIR_WORK.'file.log');
        } finally {
            restore_error_handler();
        }

        $this->assertFalse($result->isSuccessful());
        $this->assertStringContainsString('not can open', (string) $result->error);

        $this->assertFileExists(self::DIR_WORK.'file.log');

        rmdir(self::DIR_WORK.'file.log.gz');
    }
}
