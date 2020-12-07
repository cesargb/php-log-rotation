<?php

namespace Cesargb\Log\Test;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

class TestCase extends PHPUnitTestCase
{
    const DIR_WORK = __DIR__.'/files/';

    public function setUp(): void
    {
        if (! is_dir(self::DIR_WORK)) {
            mkdir(self::DIR_WORK);
        }
    }

    public function tearDown(): void
    {
        $files = array_diff(scandir(self::DIR_WORK), ['.', '..']);

        foreach ($files as $file) {
            unlink(self::DIR_WORK.'/'.$file);
        }

        rmdir(self::DIR_WORK);
    }
}
