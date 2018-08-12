<?php

namespace Cesargb\Log\Exceptions;

use Exception;

class DirectoryIsNotValid extends Exception
{
    public function __construct($dir, $message) {
        parent::__construct(sprintf('The dir %s is not writable: %s.', $dir, $message), 0, null);
    }
}
