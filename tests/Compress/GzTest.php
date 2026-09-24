<?php

namespace Cesargb\Log\Test\Processors;

use Cesargb\Log\Compress\Gz;
use Cesargb\Log\Exceptions\RotationFailed;
use Cesargb\Log\Rotation;
use Cesargb\Log\Test\TestCase;
use Exception;

class GzTest extends TestCase
{
    public function test_rotation_processor_with_gz_processor(): void
    {
        $rotation = new Rotation;

        $rotation->compress();

        $content = 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit.
                    Aenean commodo ligula eget dolor. Aenean massa. Cum sociis
                    natoque penatibus et magnis dis parturient montes, nascetur
                    ridiculus mus. Donec quam felis, ultricies nec, pellentesque
                    eu, pretium quis, sem. Nulla consequat massa quis enim.
                    Donec pede justo, fringilla vel, aliquet nec, vulputate
                    eget, arcu.';

        file_put_contents(self::DIR_WORK.'file.log', $content);

        $rotation->then(function ($fileRotated) {
            $this->assertEquals(self::DIR_WORK.'file.log.1.gz', $fileRotated);
        })->rotate(self::DIR_WORK.'file.log');

        $this->assertFileExists(self::DIR_WORK.'file.log.1.gz');

        $this->assertEquals($content, implode('', (array) gzfile(self::DIR_WORK.'file.log.1.gz')));
    }

    public function test_rotation_processor_with_gz_processor_with_level(): void
    {
        $rotation = new Rotation;

        $rotation->compress();

        $content = bin2hex('Lorem ipsum dolor sit amet, consectetuer adipiscing elit.
                    Aenean commodo ligula eget dolor. Aenean massa. Cum sociis
                    ffffffffffffffff
                    natoque penatibus et magnis dis parturient montes, nascetur
                    hhhhhhhhhhhhhhhh
                    ridiculus mus. Donec quam felis, ultricies nec, pellentesque
                    ffffffhhhhhggggx x
                    eu, pretium quis, sem. Nulla consequat massa quis enim.
                    Donec pede justo, fringilla vel, aliquet nec, vulputate
                    eget, arcu.');

        $content .= $content;

        file_put_contents(self::DIR_WORK.'file.log', $content);
        $rotation->rotate(self::DIR_WORK.'file.log');
        $sizeDefaultLevel = filesize(self::DIR_WORK.'file.log.1.gz');

        file_put_contents(self::DIR_WORK.'file.log', $content);
        $rotation->compress(1)->rotate(self::DIR_WORK.'file.log');
        $sizeMinLevel = filesize(self::DIR_WORK.'file.log.1.gz');

        file_put_contents(self::DIR_WORK.'file.log', $content);
        $rotation->compress(9)->rotate(self::DIR_WORK.'file.log');
        $sizeMaxLevel = filesize(self::DIR_WORK.'file.log.1.gz');

        $this->assertLessThan($sizeMinLevel, $sizeDefaultLevel);
        $this->assertGreaterThan($sizeMaxLevel, $sizeDefaultLevel);
    }

    public function test_rotation_processor_without_gz_processor_if_level_is_zero(): void
    {
        $rotation = new Rotation;

        $rotation->compress(0);

        $content = 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit.
                    Aenean commodo ligula eget dolor. Aenean massa. Cum sociis
                    natoque penatibus et magnis dis parturient montes, nascetur
                    ridiculus mus. Donec quam felis, ultricies nec, pellentesque
                    eu, pretium quis, sem. Nulla consequat massa quis enim.
                    Donec pede justo, fringilla vel, aliquet nec, vulputate
                    eget, arcu.';

        file_put_contents(self::DIR_WORK.'file.log', $content);

        $rotation->rotate(self::DIR_WORK.'file.log');

        $this->assertFileExists(self::DIR_WORK.'file.log.1');
        $this->assertFileDoesNotExist(self::DIR_WORK.'file.log.1.gz');

        $this->assertEquals($content, file_get_contents(self::DIR_WORK.'file.log.1'));
    }

    public function test_gz_throws_if_source_file_not_exists(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(100);

        @(new Gz)->handler(self::DIR_WORK.'missing.log');
    }

    public function test_gz_keeps_original_file_if_target_cannot_be_opened(): void
    {
        $content = 'some log content';

        file_put_contents(self::DIR_WORK.'file.log', $content);

        // Force gzopen() to fail by making the target path a directory.
        mkdir(self::DIR_WORK.'file.log.gz');

        try {
            $this->expectException(Exception::class);
            $this->expectExceptionCode(101);

            @(new Gz)->handler(self::DIR_WORK.'file.log');
        } finally {
            $this->assertFileExists(self::DIR_WORK.'file.log');
            $this->assertEquals($content, file_get_contents(self::DIR_WORK.'file.log'));

            rmdir(self::DIR_WORK.'file.log.gz');
        }
    }

    public function test_rotation_returns_false_and_keeps_rotated_file_if_compression_fails(): void
    {
        $content = 'some log content';

        file_put_contents(self::DIR_WORK.'file.log', $content);

        // Force gzopen() to fail for file.log.1 by making its target path a directory.
        // files(1) makes the processor unlink() (rather than shift away) the colliding
        // file.log.1.gz slot; unlink() silently fails on a directory, so it stays in place.
        mkdir(self::DIR_WORK.'file.log.1.gz');

        $rotation = new Rotation;

        $rotation->compress()->files(1);

        $caughtException = null;
        $finallyCalledTimes = 0;

        $rotation->catch(function (RotationFailed $exception) use (&$caughtException) {
            $caughtException = $exception;
        })->finally(function () use (&$finallyCalledTimes) {
            $finallyCalledTimes++;
        });

        try {
            $result = @$rotation->rotate(self::DIR_WORK.'file.log');

            $this->assertFalse($result);
            $this->assertNotNull($caughtException);
            $this->assertEquals(101, $caughtException->getCode());
            $this->assertEquals(1, $finallyCalledTimes);

            $this->assertFileExists(self::DIR_WORK.'file.log.1');
            $this->assertEquals($content, file_get_contents(self::DIR_WORK.'file.log.1'));
        } finally {
            rmdir(self::DIR_WORK.'file.log.1.gz');
        }
    }

    public function test_rotation_compresses_pending_file_before_rotating(): void
    {
        $previousContent = 'previous rotation left this uncompressed';
        $newContent = 'new log content';

        file_put_contents(self::DIR_WORK.'file.log.1', $previousContent);
        file_put_contents(self::DIR_WORK.'file.log', $newContent);

        $rotation = new Rotation;

        $rotation->compress();

        $this->assertTrue($rotation->rotate(self::DIR_WORK.'file.log'));

        $this->assertFileDoesNotExist(self::DIR_WORK.'file.log.1');
        $this->assertFileExists(self::DIR_WORK.'file.log.1.gz');
        $this->assertFileExists(self::DIR_WORK.'file.log.2.gz');

        $this->assertEquals($newContent, implode('', (array) gzfile(self::DIR_WORK.'file.log.1.gz')));
        $this->assertEquals($previousContent, implode('', (array) gzfile(self::DIR_WORK.'file.log.2.gz')));
    }

    public function test_rotation_aborts_if_pending_file_and_its_gz_exist(): void
    {
        $pendingContent = 'pending uncompressed file';
        $pendingGzContent = 'already compressed file';
        $newContent = 'new log content';

        file_put_contents(self::DIR_WORK.'file.log.1', $pendingContent);
        file_put_contents(self::DIR_WORK.'file.log.1.gz', $pendingGzContent);
        file_put_contents(self::DIR_WORK.'file.log', $newContent);

        $rotation = new Rotation;

        $rotation->compress();

        $caughtException = null;

        $rotation->catch(function (RotationFailed $exception) use (&$caughtException) {
            $caughtException = $exception;
        });

        $result = $rotation->rotate(self::DIR_WORK.'file.log');

        $this->assertFalse($result);
        $this->assertNotNull($caughtException);
        $this->assertEquals(24, $caughtException->getCode());

        $this->assertEquals($pendingContent, file_get_contents(self::DIR_WORK.'file.log.1'));
        $this->assertEquals($pendingGzContent, file_get_contents(self::DIR_WORK.'file.log.1.gz'));
        $this->assertEquals($newContent, file_get_contents(self::DIR_WORK.'file.log'));
    }
}
