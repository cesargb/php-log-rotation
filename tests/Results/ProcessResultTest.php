<?php

namespace Cesargb\Log\Test\Results;

use Cesargb\Log\Results\ProcessResult;
use Cesargb\Log\Test\TestCase;

class ProcessResultTest extends TestCase
{
    public function test_successful_result(): void
    {
        $result = ProcessResult::successful('file.log', 'file.log.1');

        $this->assertTrue($result->isSuccessful());
        $this->assertEquals('file.log', $result->filenameSource);
        $this->assertEquals('file.log.1', $result->filenameTarget);
        $this->assertNull($result->error);
    }

    public function test_failed_result_with_error(): void
    {
        $result = ProcessResult::failed('file.log', 'file file.log not can read.');

        $this->assertFalse($result->isSuccessful());
        $this->assertEquals('file.log', $result->filenameSource);
        $this->assertNull($result->filenameTarget);
        $this->assertEquals('file file.log not can read.', $result->error);
    }

    public function test_failed_result_without_error(): void
    {
        $result = ProcessResult::failed('file.log');

        $this->assertFalse($result->isSuccessful());
        $this->assertNull($result->error);
    }
}
