<?php

namespace Cesargb\Log;

use LogicException;
use Cesargb\Log\Processors\AbstractProcessor;

class Rotation
{
    protected $processors = [];
    protected $size       = 0;
    protected $age        = '7 DAY';

    public function getProcessors()
    {
        return $this->processors;
    }

    /**
     * Add processors to rotate
     *
     * @param AbstractProcessor ...$processors
     * @return self
     */
    public function addProcessor(AbstractProcessor ...$processors): self
    {
        $this->processors = array_merge($this->processors, $processors);

        return $this;
    }

    /**
     * @return string
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param mixed $size
     */
    public function setSize($size): self
    {
        $this->size = $size;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * @param mixed $age
     */
    public function setAge($age): self
    {
        $this->age = $age;
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

        if($this->filehasMaxSize($file))
            return true;

        if($this->filehasMaxAge($file))
            return true;

        return false;
    }

    /**
     *  Check file size is larger than defined.
     *
     * @param $file
     * @return bool
     */
    protected function filehasMaxSize($file)
    {
        $maxSize = $this->convertUserStrToBytes($this->getSize());
        if ( filesize($file) > $maxSize )
            return true;

        return false;
    }


    /**
     *  Check file age is larger than defined.
     *
     * @param $file
     * @return bool
     */
    protected function filehasMaxAge($file)
    {
        $maxAge       = $this->getAge();
        if (filemtime($file) < strtotime('-'.$maxAge))
            return true;

        return false;
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

    private function convertUserStrToBytes($str)
    {
        $str = trim($str);
        $num = (double)$str;
        if (strtoupper(substr($str, -1)) == "B")  $str = substr($str, 0, -1);
        switch (strtoupper(substr($str, -1)))
        {
            case "P":  $num *= 1024;
            case "T":  $num *= 1024;
            case "G":  $num *= 1024;
            case "M":  $num *= 1024;
            case "K":  $num *= 1024;
        }

        return $num;
    }
}
