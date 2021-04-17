<?php

namespace Cesargb\Log;

use Throwable;

trait ErrorHandler
{
    private $catchCallable = null;

    /**
     * Call function if roteted catch any Exception.
     *
     * @param callable $callable
     * @return self
     */
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
