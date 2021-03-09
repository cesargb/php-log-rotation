<?php

namespace Cesargb\Log;

use Cesargb\Log\Compress\Gz;
use LogicException;
use Cesargb\Log\Processors\RotativeProcessor;

class Rotation
{
    /**
     * @var RotativeProcessor
     */
    private $processor;

    /**
     * @var boolean
     */
    private $_compress = false;

    private int $_minSize = 0;

    public function __construct()
    {
        $this->processor = new RotativeProcessor();
    }

    /**
     * Log files are rotated count times before being removed
     *
     * @param int $count
     * @return self
     */
    public function files(int $count): self
    {
        $this->processor->files($count);

        return $this;
    }

    /**
     * Old versions of log files are compressed
     *
     * @return self
     */
    public function compress(): self
    {
        $this->_compress = true;

        $this->processor->compress();

        return $this;
    }

    /**
     * Log files are rotated when they grow bigger than size bytes
     *
     * @param integer $bytes
     * @return self
     */
    public function minSize(int $bytes): self
    {
        $this->_minSize = $bytes;

        return $this;
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

        $fileRotated = $this->runProcessor($file, $fileRotate);

        if ($fileRotated && $this->_compress) {
            $gz = new Gz();

            $fileRotated = $gz->handler($fileRotated);
        }

        return $fileRotated;
    }

    /**
     * Run processor
     *
     * @param string $originalFile
     * @param string|null $fileRotated
     * @return string|null
     */
    private function runProcessor(string $originalFile, ?string $fileRotated): ?string
    {
        $this->initProcessorFile($originalFile);

        if (!$fileRotated) {
            return null;
        }

        $fileRotated = $this->processor->handler($fileRotated);

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
        if (! $this->fileIsValid($file)) {
            throw new LogicException(sprintf('the file %s not is valid.', $file), 2);
        }

        return filesize($file) > ($this->_minSize > 0 ? $this->_minSize : 0);
    }

    /**
     * Set original File to processor
     *
     * @param string $file
     * @return void
     */
    private function initProcessorFile(string $file)
    {
        $this->processor->setFileOriginal($file);
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
