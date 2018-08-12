<?php

namespace Cesargb\Log\Handlers;

use Exception;
use Cesargb\Log\Exceptions\RotateFailed;
use Cesargb\Log\Exceptions\FileIsNotValid;
use Cesargb\Log\Exceptions\DirectoryIsNotValid;

abstract class AbstractHandler implements HandlerInterface
{

    protected $file;

    protected $compress;

    public function __construct($file, bool $compress = true)
    {
        $this->file = $file;

        $this->compress = $compress;

        clearstatcache();
    }

    protected function validateSource()
    {
        if (! is_writable($this->file)) {
            return new FileIsNotValid($this->file, 'is not writable');
        }

        return true;
    }

    protected function validateDestination($fileDestination)
    {
        $dir_destination = dirname($fileDestination);

        if (! is_dir($dir_destination)) {
            if (! file_exists($dir_destination)) {
                if (! mkdir($dir_destination, 0777, true)) {
                    return new DirectoryIsNotValid($this->file, 'Not is writable');
                }
            } else {
                return new DirectoryIsNotValid($this->file, 'Exists and is not a directory');
            }
        }

        if (! is_writable($dir_destination)) {
            return new DirectoryIsNotValid($this->file, 'Not is writable');
        }

        return true;
    }

    protected function moveTo($fileDestination)
    {
        if ($e = $this->validateSource($this->file) !== true) {
            return $e;
        }

        if ($e = $this->validateDestination($fileDestination) !== true) {
            return $e;
        }

        if (filesize($this->file) == 0) {
            return true;
        }

        $fdSource = fopen($this->file, 'r+');

        if (! $fdSource) {
            return new FileIsNotValid($this->file, 'is not writable');
        }

        if (! flock($fdSource, LOCK_EX)) {
            fclose($fdSource);

            return new FileIsNotValid($this->file, 'is not lockeable');
        }

        if (! copy($this->file, $fileDestination)) {
            fclose($fdSource);

            return new RotateFailed($this->file, $fileDestination, 'fail to copy data');
        }

        if (! ftruncate($fdSource, 0)) {
            fclose($fdSource);

            unlink($fileDestination);

            return new FileIsNotValid($this->file, 'fail to truncate');
        }

        flock($fdSource, LOCK_UN);

        fflush($fdSource);

        fclose($fdSource);

        clearstatcache();

        return true;
    }
}
