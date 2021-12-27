<?php

namespace Cesargb\Log\Test\Processors;

use Cesargb\Log\Rotation;
use Cesargb\Log\Test\TestCase;

class GzTest extends TestCase
{
    public function testRotationProcessorWithGzProcessor()
    {
        $rotation = new Rotation();

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

        // $this->assertStringEqualsFile(self::DIR_WORK.'file.log', '');

        $this->assertFileExists(self::DIR_WORK.'file.log.1.gz');

        $this->assertEquals($content, implode('', gzfile(self::DIR_WORK.'file.log.1.gz')));
    }
}
