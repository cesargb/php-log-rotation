<?php

namespace Cesargb\Log\Test;

use Cesargb\Log\Rotation;

class RotationTest extends TestCase
{
    public function testLogRotatingIfFileNotExists(): void
    {
        $rotation = new Rotation();

        $result = $rotation->rotate(self::DIR_WORK.'file.log');

        $this->assertFalse($result);
    }

    public function testNotRotateIfFileIsEmpty(): void
    {
        touch(self::DIR_WORK.'file.log');

        $rotation = new Rotation();

        $rotation->rotate(self::DIR_WORK.'file.log');

        $this->assertFileExists(self::DIR_WORK.'file.log');

        $this->assertFileDoesNotExist(self::DIR_WORK.'file.log.1');
    }

    public function testRotationDefault(): void
    {
        file_put_contents(self::DIR_WORK.'file.log', microtime(true));

        $rotation = new Rotation();

        $rotation->rotate(self::DIR_WORK.'file.log');

        $this->assertFileExists(self::DIR_WORK.'file.log.1');
    }

    public function testOptionCompress(): void
    {
        file_put_contents(self::DIR_WORK.'file.log', microtime(true));

        $rotation = new Rotation();

        $rotation->compress()->rotate(self::DIR_WORK.'file.log');

        $this->assertFileExists(self::DIR_WORK.'file.log.1.gz');
    }

    public function testOptionFiles(): void
    {
        $maxFiles = 5;

        $rotation = new Rotation();

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

    public function testOptionFilesOnlyOne(): void
    {
        $filesToCreate = 5;

        $rotation = new Rotation();

        $rotation->files(1);

        foreach (range(1, $filesToCreate + 1) as $n) {
            file_put_contents(self::DIR_WORK.'file.log', microtime(true));
            $rotation->rotate(self::DIR_WORK.'file.log');
        }

        $this->assertFileExists(self::DIR_WORK.'file.log.1');

        $this->assertFileDoesNotExist(self::DIR_WORK.'file.log.2');
    }

    public function testOptionMinsize(): void
    {
        file_put_contents(self::DIR_WORK.'file.log', microtime(true));

        $rotation = new Rotation();

        $rotation->minSize(1000)->rotate(self::DIR_WORK.'file.log');

        $this->assertFileDoesNotExist(self::DIR_WORK.'file.log.1');

        $rotation->minSize(1)->rotate(self::DIR_WORK.'file.log');

        $this->assertFileExists(self::DIR_WORK.'file.log.1');
    }

    public function testRotationTruncate(): void
    {
        file_put_contents(self::DIR_WORK.'file.log', microtime(true));

        $rotation = new Rotation();

        $rotation->truncate()->rotate(self::DIR_WORK.'file.log');

        $this->assertStringEqualsFile(self::DIR_WORK.'file.log', '');

        $this->assertFileExists(self::DIR_WORK.'file.log.1');
    }

    public function testOptionTruncateAndCompress(): void
    {
        file_put_contents(self::DIR_WORK.'file.log', microtime(true));

        $rotation = new Rotation();

        $rotation->compress()->truncate()->rotate(self::DIR_WORK.'file.log');

        $this->assertStringEqualsFile(self::DIR_WORK.'file.log', '');

        $this->assertFileExists(self::DIR_WORK.'file.log.1.gz');
    }
}
