<?php

namespace Cesargb\Log\Test;

use LogicException;
use Cesargb\Log\Rotation;
use Cesargb\Log\Test\TestCase;
use Cesargb\Log\Processors\RotativeProcessor;

class RotativeTest extends TestCase
{
    public function test_log_rotating_without_processor()
    {
        $this->expectException(LogicException::class);

        file_put_contents(self::DIR_WORK.'file.log', microtime(true));

        $rotation = new Rotation();

        $rotation->rotate(self::DIR_WORK.'file.log');

        $this->assertStringEqualsFile(self::DIR_WORK.'file.log', '');
    }

    public function test_log_rotating_if_file_not_exists()
    {
        $this->expectException(LogicException::class);

        $rotation = new Rotation();

        $rotation->addProcessor(
            (new RotativeProcessor())
                        ->setMaxFiles(1)
                        ->setFileOriginal(self::DIR_WORK.'file.log')
        );

        $result =$rotation->rotate(self::DIR_WORK.'file.log');

        $this->assertFalse($result);
    }

}
