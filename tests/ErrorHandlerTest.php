<?php

namespace Cesargb\Log\Test;

use Cesargb\Log\Exceptions\RotationFailed;
use Cesargb\Log\Rotation;

class ErrorHandlerTest extends TestCase
{
    public function testCallThenIfRotateWasSuccessful(): void
    {
        file_put_contents(self::DIR_WORK.'file.log', microtime(true));

        $rotation = new Rotation();

        $thenCalled = false;

        $rotation->then(function () use (&$thenCalled) {
            $thenCalled = true;
        })->rotate(self::DIR_WORK.'file.log');

        $this->assertTrue($thenCalled);
    }

    public function testNotCallThenIfRotateNotWasSuccessful(): void
    {
        $rotation = new Rotation();

        $thenCalled = false;

        $rotation->then(function () use (&$thenCalled) {
            $thenCalled = true;
        })->rotate(self::DIR_WORK.'file.log');

        $this->assertFalse($thenCalled);
    }

    public function testThrowsException(): void
    {
        $this->expectException(RotationFailed::class);

        $rotation = new Rotation();

        touch(self::DIR_WORK.'/file.log');
        chmod(self::DIR_WORK.'/file.log', 0444);

        $result = $rotation->rotate(self::DIR_WORK.'file.log');

        $this->assertFalse($result);
    }

    public function testCatchException(): void
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

    public function testCallFinallyIfRotateWasSuccessful(): void
    {
        file_put_contents(self::DIR_WORK.'file.log', microtime(true));

        $rotation = new Rotation();

        $finallyCalled = false;

        $rotation->finally(function () use (&$finallyCalled) {
            $finallyCalled = true;
        })->rotate(self::DIR_WORK.'file.log');

        $this->assertTrue($finallyCalled);
    }

    public function testCallFinallyIfFileDontExists(): void
    {
        $rotation = new Rotation();

        $finallyCalled = false;

        $rotation->finally(function () use (&$finallyCalled) {
            $finallyCalled = true;
        })->rotate(self::DIR_WORK.'file.log');

        $this->assertTrue($finallyCalled);
    }

    public function testCallFinallyIfThrowException(): void
    {
        $this->expectException(RotationFailed::class);

        $rotation = new Rotation();

        touch(self::DIR_WORK.'/file.log');
        chmod(self::DIR_WORK.'/file.log', 0444);

        $finallyCalled = false;

        $rotation->finally(function () use (&$finallyCalled) {
            $finallyCalled = true;
        })->rotate(self::DIR_WORK.'file.log');

        $this->assertTrue($finallyCalled);
    }
}
