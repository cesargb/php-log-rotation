<?php

namespace Cesargb\Log\Exceptions;

use Exception;
use Throwable;

class RotationFailed extends Exception
{
    private string $filename;

    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null, ?string $filename = null)
    {
        parent::__construct($message, $code, $previous);

        $this->filename = $filename ?? '';
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }
}
