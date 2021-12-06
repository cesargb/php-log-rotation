<?php

namespace Cesargb\Log\Processors;

class RotativeProcessor extends AbstractProcessor
{
    private $maxFiles = 366;

    /**
     * Log files are rotated count times before being removed.
     */
    public function files(int $count): self
    {
        $this->maxFiles = $count;

        return $this;
    }

    public function handler(string $filename): ?string
    {
        $filenameTarget = "{$this->filenameSource}.1";

        $this->rotate();

        rename($filename, $filenameTarget);

        return $this->processed($filenameTarget);
    }

    private function rotate(int $number = 1): string
    {
        $filenameTarget = "{$this->filenameSource}.{$number}{$this->extension}";

        if (!file_exists($filenameTarget)) {
            return $filenameTarget;
        }

        if ($this->maxFiles > 0 && $number >= $this->maxFiles) {
            if (file_exists($filenameTarget)) {
                unlink($filenameTarget);
            }

            return $filenameTarget;
        }

        $nextFilename = $this->rotate($number + 1);

        rename($filenameTarget, $nextFilename);

        return $filenameTarget;
    }
}
