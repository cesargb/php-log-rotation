<?php

namespace Cesargb\Log\Test;

use Cesargb\Log\Rotation;

class OptionTest extends TestCase
{
    public function testPassOptions(): void
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

    public function testCatchExceptionIfMethodIsNotPermitted(): void
    {
        $this->expectException(\LogicException::class);

        new Rotation([
            'bad-method' => null,
        ]);
    }
}
