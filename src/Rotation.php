<?php

namespace Cesargb\Log;

use LogicException;
use Cesargb\Log\Processors\AbstractProcessor;

class Rotation
{
    protected $processors = [];

    public function getProcessors()
    {
        return $this->processors;
    }

    public function addProcessor(AbstractProcessor $processor)
    {
        $this->processors[] = $processor;
    }

    /**
     * Rotate file
     *
     * @param string $file
     * @return string|null
     */
    public function rotate(string $file): ?string
    {
        if (!$this->canRotate($file)) {
            return null;
        }

        $fileRotate = $this->moveContentToTempFile($file);

        return $this->runProcessors($file, $fileRotate);
    }

    /**
     * Run all processors
     *
     * @param string $originalFile
     * @param string|null $fileRotated
     * @return string|null
     */
    private function runProcessors(string $originalFile, ?string $fileRotated): ?string
    {
        $this->initProcessorFile($originalFile);

        foreach ($this->processors as $processor) {
            if (!$fileRotated) {
                return null;
            }

            $fileRotated = $processor->handler($fileRotated);
        }

        return $fileRotated;
    }

    /**
     * check if file need rotate
     *
     * @param string $file
     * @return boolean
     * @throws LogicException
     */
    private function canRotate(string $file): bool
    {
        if (count($this->processors) == 0) {
            throw new LogicException('You need at least one processor to logs rotate', 1);
        }

        if (! $this->fileIsValid($file)) {
            throw new LogicException(sprintf('the file %s not is valid.', $file), 2);
        }

        return filesize($file) > 0;
    }

    /**
     * Set original File to processor
     *
     * @param string $file
     * @return void
     */
    private function initProcessorFile(string $file)
    {
        $this->processors[0]->setFileOriginal($file);
    }

    /**
     * check if file is valid to rotate
     *
     * @param string|null $file
     * @return boolean
     */
    private function fileIsValid(?string $file): bool
    {
        return $file && is_file($file) && is_writable($file);
    }

    /**
     * move data to temp file and truncate
     *
     * @param string $file
     * @return string|null
     */
    private function moveContentToTempFile(string $file): ?string
    {
        clearstatcache();

        $fileDestination = tempnam(dirname($file), 'LOG');

        $fd = fopen($file, 'r+');

        if (! $fd) {
            return null;
        }

        if (! flock($fd, LOCK_EX)) {
            fclose($fd);

            return null;
        }

        if (! copy($file, $fileDestination)) {
            fclose($fd);

            return null;
        }

        if (! ftruncate($fd, 0)) {
            fclose($fd);

            unlink($fileDestination);

            return null;
        }

        flock($fd, LOCK_UN);

        fflush($fd);

        fclose($fd);

        return $fileDestination;
    }
}
