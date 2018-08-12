<?php

namespace Cesargb\Log\Processors;

use Cesargb\Log\Processors\ProcessorInterface;

abstract class AbstractProcessor implements ProcessorInterface
{
    protected $continueNextProcessorIfFail = true;

    protected $fileIn;

    protected $fileOut;

    protected $fileOriginal;


    public function __construct()
    {
        clearstatcache();
    }

    public function getContinueNextProcessorIfFail()
    {
        return $this->continueNextProcessorIfFail;
    }

    public function getFileIn()
    {
        return $this->fileIn;
    }

    public function getFileOut()
    {
        return $this->fileOut;
    }

    public function setFileOriginal($fileOriginal)
    {
        $this->fileOriginal = $fileOriginal;

        return $this;
    }

    public function setOverWrite($overWrite)
    {
        $this->overWrite = $overWrite;

        return $this;
    }

    public function handler($file)
    {
        $this->fileIn = $file;

        $this->fileOut = false;
    }

    public function processed($file)
    {
        if (is_file($file)) {
            $this->fileOut = $file;

            return $this->fileOut;
        }

        return false;
    }
}
