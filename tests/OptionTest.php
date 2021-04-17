<?php

namespace Cesargb\Log\Test;

use Exception;
use Cesargb\Log\Rotation;
use Cesargb\Log\Test\TestCase;

class OptionTest extends TestCase
{
    public function test_pass_options()
    {
        $rotation = new Rotation([
            'files' => 1,
            'compress' => true,
            'min-size' => 10,
            'then' => function ($filename) {},
            'catch' => function ($error) {},
        ]);

        $this->assertNotNull($rotation);
    }

    public function test_catch_exceptio_if_method_is_not_permited()
    {
        $this->expectException(\LogicException::class);

        $r = new Rotation([
            'bad-method' => null,
        ]);
    }
}
