<?php

namespace Cesargb\Log\Test\Processors;

use Cesargb\Log\Rotation;
use Cesargb\Log\Test\TestCase;

class RotativeProcessorTest extends TestCase
{
    public function testRotationProcessor()
    {
        $maxFiles = 5;

        $rotation = new Rotation();

        $rotation->files(5);

        foreach (range(1, $maxFiles + 1) as $n) {
            file_put_contents(self::DIR_WORK.'file.log', microtime(true));
            $rotation->rotate(self::DIR_WORK.'file.log');
        }

        // $this->assertStringEqualsFile(self::DIR_WORK.'file.log', '');

        foreach (range(1, $maxFiles) as $n) {
            $this->assertFileExists(self::DIR_WORK.'file.log.'.$n);
        }

        $this->assertFalse(is_file(self::DIR_WORK.'file.log.'.($maxFiles + 1)));
    }

    public function testRotationProcessorWithGzProcessor()
    {
        $maxFiles = 5;

        $rotation = new Rotation();

        $rotation->compress()->files(5);

        foreach (range(1, $maxFiles + 1) as $n) {
            file_put_contents(self::DIR_WORK.'file.log', microtime(true));

            $rotation->rotate(self::DIR_WORK.'file.log');
        }

        // $this->assertStringEqualsFile(self::DIR_WORK.'file.log', '');

        foreach (range(1, $maxFiles) as $n) {
            $this->assertFileExists(self::DIR_WORK."file.log.{$n}.gz");
        }

        $numeralCleaned = $maxFiles + 1;

        $this->assertFalse(is_file(self::DIR_WORK."file.log.{$numeralCleaned}.gz"));
    }

    public function testRotationProcessorIssue10()
    {
        $maxFiles = 5;

        $rotation = new Rotation();

        $rotation->files(5);

        file_put_contents(self::DIR_WORK.'error.log.1.1', microtime());

        foreach (range(1, $maxFiles + 1) as $n) {
            $content = microtime();

            file_put_contents(self::DIR_WORK.'error.log.1', $content);

            $rotation
                ->then(function ($f1, $f2) {
                    $this->assertEquals($f1, self::DIR_WORK.'error.log.1.1');
                    $this->assertEquals($f2, self::DIR_WORK.'error.log.1');
                })
                ->rotate(self::DIR_WORK.'error.log.1');

            $this->assertEquals($content, file_get_contents(self::DIR_WORK.'error.log.1.1'));

        }

        foreach (range(1, $maxFiles) as $n) {
            $this->assertFileExists(self::DIR_WORK.'error.log.1.'.$n);
        }

        $this->assertFalse(is_file(self::DIR_WORK.'error.log.1.'.($maxFiles + 1)));
    }

}
