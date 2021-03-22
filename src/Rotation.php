<?php

namespace Cesargb\Log;

use Cesargb\Log\Compress\Gz;
use Cesargb\Log\Processors\RotativeProcessor;
use Exception;

class Rotation
{
    private RotativeProcessor $processor;

    private bool $_compress = false;

    private int $_minSize = 0;

    private $thenCallback = null;

    private $errorHandler = null;

    public function __construct()
    {
        $this->processor = new RotativeProcessor();

        $this->errorHandler = new ErrorHandler();
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
     * Call function if roteted was sucessfull and pass
     * the file as argument.
     *
     * @param callable $callable
     * @return self
     */
    public function then(callable $callable): self
    {
        $this->thenCallback = $callable;

        return $this;
    }

    /**
     * Call function if roteted catch any Exception.
     *
     * @param callable $callable
     * @return self
     */
    public function catch(callable $callable): self
    {
        $this->errorHandler->catch($callable);

        return $this;
    }

    /**
     * Rotate file
     *
     * @param string $file
     * @return boolean true if rotated was successful
     */
    public function rotate(string $file): bool
    {
        if (!$this->canRotate($file)) {
            return false;
        }

        $fileRotate = $this->moveContentToTempFile($file);

        $fileRotated = $this->runProcessor($file, $fileRotate);

        if ($fileRotated && $this->_compress) {
            $gz = new Gz();

            try {
                $fileRotated = $gz->handler($fileRotated);
            } catch (Exception $error) {
                $this->errorHandler->exception($error);

                $fileRotated = null;
            }

        }

        if ($fileRotated && $this->thenCallback) {
            call_user_func($this->thenCallback, $fileRotated);
        }

        return ! empty($fileRotated);
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
     */
    private function canRotate(string $file): bool
    {
        if (! $this->fileIsValid($file)) {
            $this->errorHandler->exception(
                new Exception(sprintf('the file %s not is valid.', $file), 10)
            );

            return false;
        }

        return filesize($file) > ($this->_minSize > 0 ? $this->_minSize : 0);
    }

    /**
     * Set original File to processor
     *
     * @param string $file
     * @return void
     */
    private function initProcessorFile(string $file): void
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
            $this->errorHandler->exception(
                new Exception(sprintf('the file %s not can open.', $file), 20)
            );

            return null;
        }

        if (! flock($fd, LOCK_EX)) {
            fclose($fd);

            $this->errorHandler->exception(
                new Exception(sprintf('the file %s not can lock.', $file), 21)
            );

            return null;
        }

        if (! copy($file, $fileDestination)) {
            fclose($fd);

            $this->errorHandler->exception(
                new Exception(
                    sprintf('the file %s not can copy to temp file %s.', $file, $fileDestination),
                    22
                )
            );

            return null;
        }

        if (! ftruncate($fd, 0)) {
            fclose($fd);

            unlink($fileDestination);

            $this->errorHandler->exception(
                new Exception(sprintf('the file %s not can truncate.', $file), 23)
            );

            return null;
        }

        flock($fd, LOCK_UN);

        fflush($fd);

        fclose($fd);

        return $fileDestination;
    }
}
