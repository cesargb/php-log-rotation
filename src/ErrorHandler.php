<?php

namespace Cesargb\Log;

use Cesargb\Log\Exceptions\RotationFailed;
use Throwable;

trait ErrorHandler
{
    private $catchCallable = null;

    protected $finallyCallback = null;

    private ?string $_filename = null;

    /**
     * Call function if roteted catch any Exception.
     */
    public function catch(callable $callable): self
    {
        $this->catchCallable = $callable;

        return $this;
    }

    protected function setFilename(string $filename): void
    {
        $this->_filename = $filename;
    }

    protected function exception(Throwable $exception): self
    {
        $this->finished($this->_filename, $exception->getMessage());

        if ($this->catchCallable) {
            call_user_func($this->catchCallable, $this->convertException($exception));
        } else {
            throw $this->convertException($exception);
        }

        return $this;
    }

    protected function finished(string $filenameSource, string $message): void
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
