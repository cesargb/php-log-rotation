<?php

declare(strict_types=1);

namespace Cesargb\Log\Results;

use Cesargb\Log\Exceptions\ProcessException;

final class ProcessResult
{
    private function __construct(
        public readonly string $filenameSource,
        public readonly ?string $filenameTarget,
        public readonly ?ProcessException $exception = null,
    ) {}

    public static function successful(string $filenameSource, string $filenameTarget): self
    {
        return new self($filenameSource, $filenameTarget);
    }

    public static function failed(string $filenameSource, ProcessException $exception): self
    {
        return new self($filenameSource, null, $exception);
    }

    public function isSuccessful(): bool
    {
        return $this->filenameTarget !== null;
    }
}
