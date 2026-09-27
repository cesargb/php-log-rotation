<?php

namespace Cesargb\Log\Test\Results;

use Cesargb\Log\Results\ProcessResult;
use Cesargb\Log\Test\TestCase;
use Exception;

class ProcessResultTest extends TestCase
{
    public function test_successful_result(): void
    {
        $result = ProcessResult::successful('file.log', 'file.log.1');

        $this->assertTrue($result->isSuccessful());
        $this->assertEquals('file.log', $result->filenameSource);
        $this->assertEquals('file.log.1', $result->filenameTarget);
        $this->assertNull($result->exception);
    }

    public function test_failed_result_with_exception(): void
    {
        $result = ProcessResult::failed('file.log', new Exception('file file.log not can read.', 100));

        $this->assertFalse($result->isSuccessful());
        $this->assertEquals('file.log', $result->filenameSource);
        $this->assertNull($result->filenameTarget);
        $this->assertEquals('file file.log not can read.', $result->exception?->getMessage());
        $this->assertEquals(100, $result->exception?->getCode());
    }
}
