<?php

namespace Cesargb\Log;

use Throwable;

trait ErrorHandler
{
    private $catchCallable = null;

    /**
     * Call function if roteted catch any Exception.
     */
    public function catch(callable $callable): self
    {
        $this->catchCallable = $callable;

        return $this;
    }

    protected function exception(Throwable $exception): self
    {
        if ($this->catchCallable) {
            call_user_func($this->catchCallable, $exception);
        } else {
            throw $exception;
        }

        return $this;
    }
}
