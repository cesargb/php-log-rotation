<?php

namespace Cesargb\Log\Exceptions;

use Exception;

class RotateFailed extends Exception
{
    public function __construct($fileSource, $fileDestination, $message = '') {
        parent::__construct(sprintf('Fail to move data from file %s to %s: %s.', $fileSource, $fileDestination, $message), 0, null);
    }
}
