<?php

namespace Cesargb\Log\Test\Processors;

use Cesargb\Log\Rotation;
use Cesargb\Log\Test\TestCase;

class GzTest extends TestCase
{
    public function testRotationProcessorWithGzProcessor(): void
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

        $this->assertFileExists(self::DIR_WORK.'file.log.1.gz');

        $this->assertEquals($content, implode('', (array)gzfile(self::DIR_WORK.'file.log.1.gz')));
    }

    public function testRotationProcessorWithGzProcessorWithLevel(): void
    {
        $rotation = new Rotation();

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

    public function testRotationProcessorWithoutGzProcessorIfLevelIsZero(): void
    {
        $rotation = new Rotation();

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
}
