<?php

namespace Cesargb\Log\Test;

use Cesargb\Log\Rotation;

class OptionTest extends TestCase
{
    public function test_pass_options(): void
    {
        $this->expectNotToPerformAssertions();

        new Rotation([
            'files' => 1,
            'compress' => true,
            'min-size' => 10,
            'truncate' => false,
            'then' => function ($filename) {},
            'catch' => function ($error) {},
            'finally' => function ($message) {},
        ]);
    }

    public function test_catch_exception_if_method_is_not_permitted(): void
    {
        $this->expectException(\LogicException::class);

        new Rotation([
            'bad-method' => null,
        ]);
    }
}
