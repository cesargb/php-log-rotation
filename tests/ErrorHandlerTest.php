<?php

namespace Cesargb\Log\Test;

use Cesargb\Log\Exceptions\RotationFailed;
use Cesargb\Log\Rotation;

class ErrorHandlerTest extends TestCase
{
    public function testThrowsException()
    {
        $this->expectException(RotationFailed::class);

        $rotation = new Rotation();

        touch(self::DIR_WORK.'/file.log');
        chmod(self::DIR_WORK.'/file.log', 0444);

        $result = $rotation->rotate(self::DIR_WORK.'file.log');

        $this->assertFalse($result);
    }

    public function testCatchException()
    {
        $rotation = new Rotation();

        touch(self::DIR_WORK.'/file.log');
        chmod(self::DIR_WORK.'/file.log', 0444);

        $result = $rotation
            ->catch(function (RotationFailed $exception) {
                $this->assertEquals(
                    self::DIR_WORK.'file.log',
                    $exception->getFilename()
                );
            })
            ->rotate(self::DIR_WORK.'file.log');

        $this->assertFalse($result);
    }
}
