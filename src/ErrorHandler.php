<?php

namespace Cesargb\Log;

use Throwable;

class ErrorHandler
{
    private $catchCallable = null;

    public function catch(callable $callable): self
    {
        $this->catchCallable = $callable;

        return $this;
    }

    public function exception(Throwable $exception): self
    {
        if ($this->catchCallable) {
            call_user_func($this->catchCallable, $exception);
        } else {
            throw $exception;
        }

        return $this;
    }
}
