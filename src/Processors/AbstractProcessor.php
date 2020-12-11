<?php

namespace Cesargb\Log\Processors;

abstract class AbstractProcessor
{
    private $fileOut;

    protected $fileOriginal;

    abstract public function handler($file): ?string;

    public function __construct()
    {
        clearstatcache();
    }

    public function setFileOriginal($fileOriginal)
    {
        $this->fileOriginal = $fileOriginal;

        return $this;
    }

    protected function processed($file): ?string
    {
        if (is_file($file)) {
            $this->fileOut = $file;

            return $this->fileOut;
        }

        return null;
    }
}
