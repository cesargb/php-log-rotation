<?php

namespace Cesargb\Log;

use Cesargb\Log\Exceptions\RotationFailed;
use Throwable;

trait ErrorHandler
{
    private $thenCallback = null;

    private $catchCallable = null;

    private $finallyCallback = null;

    private ?string $_filename = null;

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
     * Call function if roteted catch any Exception.
     */
    public function catch(callable $callable): self
    {
        $this->catchCallable = $callable;

        return $this;
    }

    /**
     * Function that will be executed when the process was finished.
     */
    public function finally(callable $callable): self
    {
        $this->finallyCallback = $callable;

        return $this;
    }

    protected function setFilename(string $filename): void
    {
        $this->_filename = $filename;
    }

    private function sucessfull(string $filenameSource, ?string $filenameRotated): void
    {
        $this->finished('sucessfull', $filenameSource);

        if (is_null($this->thenCallback) || is_null($filenameRotated)) {
            return;
        }

        call_user_func($this->thenCallback, $filenameRotated, $filenameSource);
    }

    protected function exception(Throwable $exception): self
    {
        $this->finished($exception->getMessage(), $this->_filename);

        if ($this->catchCallable) {
            call_user_func($this->catchCallable, $this->convertException($exception));
        } else {
            throw $this->convertException($exception);
        }

        return $this;
    }


    protected function finished(string $message, ?string $filenameSource): void
    {
        if (is_null($this->finallyCallback)) {
            return;
        }

        call_user_func($this->finallyCallback, $message, $filenameSource);
    }

    private function convertException(Throwable $exception): RotationFailed
    {
        return new RotationFailed(
            $exception->getMessage(),
            $exception->getCode(),
            $exception->getPrevious(),
            $this->_filename
        );
    }
}
