<?php

namespace Cesargb\Log\Exceptions;

use Exception;

class FileIsNotValid extends Exception
{
    public function __construct($file, $message) {
        parent::__construct(sprintf('The file %s %s.', $file, $message), 0, null);
    }
}
