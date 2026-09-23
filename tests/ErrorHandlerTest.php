<?php

namespace Cesargb\Log\Test;

use Cesargb\Log\Exceptions\RotationFailed;
use Cesargb\Log\Rotation;

class ErrorHandlerTest extends TestCase
{
    public function test_call_then_if_rotate_was_successful(): void
    {
        file_put_contents(self::DIR_WORK.'file.log', microtime(true));

        $rotation = new Rotation;

        $thenCalled = false;

        $rotation->then(function () use (&$thenCalled) {
            $thenCalled = true;
        })->rotate(self::DIR_WORK.'file.log');

        $this->assertTrue($thenCalled);
    }

    public function test_not_call_then_if_rotate_not_was_successful(): void
    {
        $rotation = new Rotation;

        $thenCalled = false;

        $rotation->then(function () use (&$thenCalled) {
            $thenCalled = true;
        })->rotate(self::DIR_WORK.'file.log');

        $this->assertFalse($thenCalled);
    }

    public function test_throws_exception(): void
    {
        $this->expectException(RotationFailed::class);

        $rotation = new Rotation;

        touch(self::DIR_WORK.'/file.log');
        chmod(self::DIR_WORK.'/file.log', 0444);

        $result = $rotation->rotate(self::DIR_WORK.'file.log');

        $this->assertFalse($result);
    }

    public function test_catch_exception(): void
    {
        $rotation = new Rotation;

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

    public function test_call_finally_if_rotate_was_successful(): void
    {
        file_put_contents(self::DIR_WORK.'file.log', microtime(true));

        $rotation = new Rotation;

        $finallyCalled = false;

        $rotation->finally(function () use (&$finallyCalled) {
            $finallyCalled = true;
        })->rotate(self::DIR_WORK.'file.log');

        $this->assertTrue($finallyCalled);
    }

    public function test_call_finally_if_file_dont_exists(): void
    {
        $rotation = new Rotation;

        $finallyCalled = false;

        $rotation->finally(function () use (&$finallyCalled) {
            $finallyCalled = true;
        })->rotate(self::DIR_WORK.'file.log');

        $this->assertTrue($finallyCalled);
    }

    public function test_call_finally_if_throw_exception(): void
    {
        $this->expectException(RotationFailed::class);

        $rotation = new Rotation;

        touch(self::DIR_WORK.'/file.log');
        chmod(self::DIR_WORK.'/file.log', 0444);

        $finallyCalled = false;

        $rotation->finally(function () use (&$finallyCalled) {
            $finallyCalled = true;
        })->rotate(self::DIR_WORK.'file.log');

        $this->assertTrue($finallyCalled);
    }
}
