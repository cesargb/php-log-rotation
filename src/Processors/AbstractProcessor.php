<?php

namespace Cesargb\Log\Processors;

abstract class AbstractProcessor
{
    protected string $filenameSource;

    protected string $extension = '';

    abstract public function handler(string $filename): ?string;

    public function __construct()
    {
        clearstatcache();
    }

    public function addExtension(string $extension): void
    {
        $this->extension = '.'.$extension;
    }

    public function removeExtension(string $extension): void
    {
        $this->extension = str_replace('.'.$extension, '', $this->extension);
    }

    public function setFilenameSource(string $filenameSource): self
    {
        $this->filenameSource = $filenameSource;

        return $this;
    }

    protected function processed(string $filename): ?string
    {
        return is_file($filename) ? $filename : null;
    }
}
