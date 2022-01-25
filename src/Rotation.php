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

    private bool $_truncate = false;

    public function __construct(array $options = [])
    {
        $this->processor = new RotativeProcessor();

        $this->methodsOptionables([
            'compress',
            'truncate',
            'minSize',
            'files',
            'then',
            'catch',
            'finally',
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
     * Truncate the original log file in place after creating a copy, instead of
     * moving the old log file.
     *
     * It can be used when some program cannot be told to close its logfile and
     * thus might continue writing (appending) to the previous log file forever.
     */
    public function truncate(bool $truncate = true): self
    {
        $this->_truncate = $truncate;

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
     * Rotate file.
     *
     * @return bool true if rotated was successful
     */
    public function rotate(string $filename): bool
    {
        $this->setFilename($filename);

        if (!$this->canRotate($filename)) {
            return false;
        }

        $fileTemporary = $this->_truncate
            ? $this->copyAndTruncate($filename)
            : $this->move($filename);

        if (is_null($fileTemporary)) {
            return false;
        }

        $fileTarget = $this->runProcessor(
            $filename,
            $fileTemporary
        );

        if (is_null($fileTarget)) {
            return false;
        }

        $fileTarget = $this->runCompress($fileTarget);

        $this->sucessfull($filename, $fileTarget);

        return true;
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

    private function runCompress(string $filename): ?string
    {
        if (!$this->_compress) {
            return $filename;
        }

        $gz = new Gz();

        try {
            return $gz->handler($filename);
        } catch (Exception $error) {
            $this->exception($error);

            return null;
        }
    }

    /**
     * check if file need rotate.
     */
    private function canRotate(string $filename): bool
    {
        if (!file_exists($filename)) {
            $this->finished(sprintf('the file %s not exists.', $filename), $filename);

            return false;
        }

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
    private function fileIsValid(string $filename): bool
    {
        return is_file($filename) && is_writable($filename);
    }

    /**
     * copy data to temp file and truncate.
     */
    private function copyAndTruncate(string $filename): ?string
    {
        clearstatcache();

        $filenameTarget = tempnam(dirname($filename), 'LOG');

        $fd = fopen($filename, 'r+');

        if ($fd === false) {
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

    private function move(string $filename): ?string
    {
        clearstatcache();

        $filenameTarget = tempnam(dirname($filename), 'LOG');

        if (!rename($filename, $filenameTarget)) {
            $this->exception(
                new Exception(
                    sprintf('the file %s not can move to temp file %s.', $filename, $filenameTarget),
                    22
                )
            );

            return null;
        }

        return $filenameTarget;
    }
}
