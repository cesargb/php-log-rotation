<?php

namespace Cesargb\Log\Test;

use Exception;
use Cesargb\Log\Rotation;
use Cesargb\Log\Test\TestCase;

class ErrorHandlerTest extends TestCase
{
    public function test_throws_exception()
    {
        $this->expectException(Exception::class);

        $rotation = new Rotation();

        $result = $rotation->rotate(self::DIR_WORK.'file.log');

        $this->assertFalse($result);
    }

    public function test_catch_exception()
    {
        $rotation = new Rotation();

        $result = $rotation
            ->catch(function ($error) {

            })
            ->rotate(self::DIR_WORK.'file.log');

        $this->assertFalse($result);
    }
}
