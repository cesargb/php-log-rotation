<?php

namespace Cesargb\Log\Test;

use Cesargb\Log\Rotation;

class RotationTest extends TestCase
{
    public function test_log_rotating_if_file_not_exists(): void
    {
        $rotation = new Rotation;

        $result = $rotation->rotate(self::DIR_WORK.'file.log');

        $this->assertFalse($result);
    }

    public function test_not_rotate_if_file_is_empty(): void
    {
        touch(self::DIR_WORK.'file.log');

        $rotation = new Rotation;

        $rotation->rotate(self::DIR_WORK.'file.log');

        $this->assertFileExists(self::DIR_WORK.'file.log');

        $this->assertFileDoesNotExist(self::DIR_WORK.'file.log.1');
    }

    public function test_rotation_default(): void
    {
        file_put_contents(self::DIR_WORK.'file.log', microtime(true));

        $rotation = new Rotation;

        $rotation->rotate(self::DIR_WORK.'file.log');

        $this->assertFileExists(self::DIR_WORK.'file.log.1');
    }

    public function test_option_compress(): void
    {
        file_put_contents(self::DIR_WORK.'file.log', microtime(true));

        $rotation = new Rotation;

        $rotation->compress()->rotate(self::DIR_WORK.'file.log');

        $this->assertFileExists(self::DIR_WORK.'file.log.1.gz');
    }

    public function test_option_files(): void
    {
        $maxFiles = 5;

        $rotation = new Rotation;

        $rotation->files(5);

        foreach (range(1, $maxFiles + 1) as $n) {
            file_put_contents(self::DIR_WORK.'file.log', microtime(true));
            $rotation->rotate(self::DIR_WORK.'file.log');
        }

        foreach (range(1, $maxFiles) as $n) {
            $this->assertFileExists(self::DIR_WORK.'file.log.'.$n);
        }

        $this->assertFileDoesNotExist(self::DIR_WORK.'file.log.'.($maxFiles + 1));
    }

    public function test_option_files_only_one(): void
    {
        $filesToCreate = 5;

        $rotation = new Rotation;

        $rotation->files(1);

        foreach (range(1, $filesToCreate + 1) as $n) {
            file_put_contents(self::DIR_WORK.'file.log', microtime(true));
            $rotation->rotate(self::DIR_WORK.'file.log');
        }

        $this->assertFileExists(self::DIR_WORK.'file.log.1');

        $this->assertFileDoesNotExist(self::DIR_WORK.'file.log.2');
    }

    public function test_option_minsize(): void
    {
        file_put_contents(self::DIR_WORK.'file.log', microtime(true));

        $rotation = new Rotation;

        $rotation->minSize(1000)->rotate(self::DIR_WORK.'file.log');

        $this->assertFileDoesNotExist(self::DIR_WORK.'file.log.1');

        $rotation->minSize(1)->rotate(self::DIR_WORK.'file.log');

        $this->assertFileExists(self::DIR_WORK.'file.log.1');
    }

    public function test_rotation_truncate(): void
    {
        file_put_contents(self::DIR_WORK.'file.log', microtime(true));

        $rotation = new Rotation;

        $rotation->truncate()->rotate(self::DIR_WORK.'file.log');

        $this->assertStringEqualsFile(self::DIR_WORK.'file.log', '');

        $this->assertFileExists(self::DIR_WORK.'file.log.1');
    }

    public function test_option_truncate_and_compress(): void
    {
        file_put_contents(self::DIR_WORK.'file.log', microtime(true));

        $rotation = new Rotation;

        $rotation->compress()->truncate()->rotate(self::DIR_WORK.'file.log');

        $this->assertStringEqualsFile(self::DIR_WORK.'file.log', '');

        $this->assertFileExists(self::DIR_WORK.'file.log.1.gz');
    }
}
