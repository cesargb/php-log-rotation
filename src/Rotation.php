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

    public function rotate($file)
    {
        if (count($this->processors) == 0) {
            throw new LogicException('You need at least one processor to logs rotate', 1);

            return false;
        }

        foreach ($this->processors as $processor) {
            $processor->setFileOriginal($file);
        }

        clearstatcache();

        if (! $this->fileIsValid($file)) {
            throw new LogicException(sprintf('the file %s not is valid.', $file), 2);

            return false;
        }

        $fileRotate = $this->extractData($file);

        if ($fileRotate !== false) {
            foreach ($this->processors as $processor) {
                $fileNew = $processor->handler($fileRotate);

                if ($fileNew === false && ! $processor->getContinueNextProcessorIfFail()) {
                    return false;
                } else {
                    $fileRotate = $fileNew;
                }
            }

        }

        return $fileRotate;
    }

    protected function fileIsValid($file)
    {
        return is_file($file) && is_writable($file);
    }

    protected function extractData($file)
    {
        if (filesize($file) == 0) {
            return false;
        }

        $fileDestination = tempnam(dirname($file), 'LOG');

        $fd = fopen($file, 'r+');

        if (! $fd) {
            return false;
        }

        if (! flock($fd, LOCK_EX)) {
            fclose($fd);

            return false;
        }

        if (! copy($file, $fileDestination)) {
            fclose($fd);

            return false;
        }

        if (! ftruncate($fd, 0)) {
            fclose($fd);

            unlink($fileDestination);

            return false;
        }

        flock($fd, LOCK_UN);

        fflush($fd);

        fclose($fd);

        return $fileDestination;
    }
}
