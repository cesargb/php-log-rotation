<?php

namespace Cesargb\Log\Test\Processors;

use LogicException;
use Cesargb\Log\Rotation;
use Cesargb\Log\Test\TestCase;
use Cesargb\Log\Processors\GzProcessor;
use Cesargb\Log\Processors\RotativeProcessor;

class RotativeProcessorTest extends TestCase
{
    public function test_rotation_processor()
    {
        $maxFiles = 5;

        $rotation = new Rotation();

        $rotation->addProcessor(
            (new RotativeProcessor())->setMaxFiles($maxFiles)
        );

        foreach (range(1, $maxFiles + 1) as $n) {
            file_put_contents(self::DIR_WORK.'file.log', microtime(true));
            $rotation->rotate(self::DIR_WORK.'file.log');
        }

        $this->assertStringEqualsFile(self::DIR_WORK.'file.log', '');

        foreach (range(1, $maxFiles) as $n) {
            $this->assertFileExists(self::DIR_WORK.'file.log.'.$n);
        }

        $this->assertFalse(is_file(self::DIR_WORK.'file.log.'.($maxFiles + 1)));
    }

    public function test_rotation_processor_with_gz_processor()
    {
        $maxFiles = 5;

        $rotation = new Rotation();

        $rotation->addProcessor(
            new GzProcessor(),
            (new RotativeProcessor())
                        ->setMaxFiles($maxFiles)
                        ->setFileOriginal(self::DIR_WORK.'file.log')
        );

        foreach (range(1, $maxFiles + 1) as $n) {
            file_put_contents(self::DIR_WORK.'file.log', microtime(true));
            $rotation->rotate(self::DIR_WORK.'file.log');
        }

        $this->assertStringEqualsFile(self::DIR_WORK.'file.log', '');

        foreach (range(1, $maxFiles) as $n) {
            $this->assertFileExists(self::DIR_WORK.'file.log.gz.'.$n);
        }

        $this->assertFalse(is_file(self::DIR_WORK.'file.log.gz'.($maxFiles + 1)));
    }
}
