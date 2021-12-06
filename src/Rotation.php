<?php

namespace Cesargb\Log;

use Cesargb\Log\Compress\Gz;
use Cesargb\Log\Processors\RotativeProcessor;
use Exception;

class Rotation
{
    use Optionable;
    use ErrorHandler;

    private RotativeProcessor $processor;

    private bool $_compress = false;

    private int $_minSize = 0;

    private $thenCallback = null;

    public function __construct(array $options = [])
    {
        $this->processor = new RotativeProcessor();

        $this->methodsOptionables([
            'compress',
            'minSize',
            'files',
            'then',
            'catch',
        ]);

        $this->options($options);
    }

    /**
     * Log files are rotated count times before being removed.
     */
    public function files(int $count): self
    {
        $this->processor->files($count);

        return $this;
    }

    /**
     * Old versions of log files are compressed.
     */
    public function compress(bool $compress = true): self
    {
        $this->_compress = $compress;

        if ($compress) {
            $this->processor->addExtension('gz');
        } else {
            $this->processor->removeExtention('gz');
        }

        return $this;
    }

    /**
     * Log files are rotated when they grow bigger than size bytes.
     */
    public function minSize(int $bytes): self
    {
        $this->_minSize = $bytes;

        return $this;
    }

    /**
     * Function that will be executed when the rotation is successful.
     * The first argument will be the name of the destination file and
     * the second the name of the rotated file.
     */
    public function then(callable $callable): self
    {
        $this->thenCallback = $callable;

        return $this;
    }

    /**
     * Rotate file.
     *
     * @return bool true if rotated was successful
     */
    public function rotate(string $filename): bool
    {
        if (!$this->canRotate($filename)) {
            return false;
        }

        $fileNameTemp = $this->moveContentToTempFile($filename);

        $filenameRotated = $this->runProcessor($filename, $fileNameTemp);

        if ($filenameRotated && $this->_compress) {
            $gz = new Gz();

            try {
                $filenameRotated = $gz->handler($filenameRotated);
            } catch (Exception $error) {
                $this->exception($error);

                $filenameRotated = null;
            }
        }

        if ($filenameRotated && $this->thenCallback) {
            call_user_func($this->thenCallback, $filenameRotated, $filename);
        }

        return !empty($filenameRotated);
    }

    /**
     * Run processor.
     */
    private function runProcessor(string $filenameSource, ?string $filenameTarget): ?string
    {
        $this->initProcessorFile($filenameSource);

        if (!$filenameTarget) {
            return null;
        }

        return $this->processor->handler($filenameTarget);
    }

    /**
     * check if file need rotate.
     */
    private function canRotate(string $filename): bool
    {
        if (!$this->fileIsValid($filename)) {
            $this->exception(
                new Exception(sprintf('the file %s not is valid.', $filename), 10)
            );

            return false;
        }

        return filesize($filename) > ($this->_minSize > 0 ? $this->_minSize : 0);
    }

    /**
     * Set original File to processor.
     */
    private function initProcessorFile(string $filename): void
    {
        $this->processor->setFilenameSource($filename);
    }

    /**
     * check if file is valid to rotate.
     */
    private function fileIsValid(?string $filename): bool
    {
        return $filename && is_file($filename) && is_writable($filename);
    }

    /**
     * move data to temp file and truncate.
     */
    private function moveContentToTempFile(string $filename): ?string
    {
        clearstatcache();

        $filenameTarget = tempnam(dirname($filename), 'LOG');

        $fd = fopen($filename, 'r+');

        if (!$fd) {
            $this->exception(
                new Exception(sprintf('the file %s not can open.', $filename), 20)
            );

            return null;
        }

        if (!flock($fd, LOCK_EX)) {
            fclose($fd);

            $this->exception(
                new Exception(sprintf('the file %s not can lock.', $filename), 21)
            );

            return null;
        }

        if (!copy($filename, $filenameTarget)) {
            fclose($fd);

            $this->exception(
                new Exception(
                    sprintf('the file %s not can copy to temp file %s.', $filename, $filenameTarget),
                    22
                )
            );

            return null;
        }

        if (!ftruncate($fd, 0)) {
            fclose($fd);

            unlink($filenameTarget);

            $this->exception(
                new Exception(sprintf('the file %s not can truncate.', $filename), 23)
            );

            return null;
        }

        flock($fd, LOCK_UN);

        fflush($fd);

        fclose($fd);

        return $filenameTarget;
    }
}
