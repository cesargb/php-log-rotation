<?php

declare(strict_types=1);

namespace Cesargb\Log\Results;

final class ProcessResult
{
    private function __construct(
        public readonly string $filenameSource,
        public readonly ?string $filenameTarget,
        public readonly ?string $error = null,
    ) {}

    public static function successful(string $filenameSource, string $filenameTarget): self
    {
        return new self($filenameSource, $filenameTarget);
    }

    public static function failed(string $filenameSource, ?string $error = null): self
    {
        return new self($filenameSource, null, $error);
    }

    public function isSuccessful(): bool
    {
        return $this->filenameTarget !== null;
    }
}
