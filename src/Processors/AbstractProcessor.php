<?php

namespace Cesargb\Log\Processors;

abstract class AbstractProcessor
{
    private string $fileOut;

    protected string $fileOriginal;

    protected string $suffix = '';

    abstract public function handler($file): ?string;

    public function __construct()
    {
        clearstatcache();
    }

    public function compress(): void
    {
        $this->suffix = '.gz';
    }

    public function setFileOriginal($fileOriginal): self
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
