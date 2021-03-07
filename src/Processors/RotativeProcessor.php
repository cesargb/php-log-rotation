<?php

namespace Cesargb\Log\Processors;

class RotativeProcessor extends AbstractProcessor
{
    private $maxFiles = 366;

    /**
     * Log files are rotated count times before being removed
     *
     * @param int $count
     * @return self
     */
    public function files(int $count): self
    {
        $this->maxFiles = $count;

        return $this;
    }

    public function handler($file): ?string
    {
        $nextFile = "{$this->fileOriginal}.1";

        $this->rotate();

        rename($file, $nextFile);

        return $this->processed($nextFile);
    }

    private function rotate(int $number = 1)
    {
        $file = "{$this->fileOriginal}.{$number}{$this->suffix}";

        if (!file_exists($file)) {
            return "{$this->fileOriginal}.{$number}{$this->suffix}";
        }

        if ($this->maxFiles > 0 && $number >= $this->maxFiles ) {
            if (file_exists($file)) {
                unlink($file);
            }

            return "{$this->fileOriginal}.{$number}{$this->suffix}";
        }

        $nextFile = $this->rotate($number + 1);

        rename($file, $nextFile);

        return "{$this->fileOriginal}.{$number}{$this->suffix}";


    }

    private function getnumber(string $file): ?int
    {
        $fileName = basename($file);
        $fileOriginaleName = basename($this->fileOriginal);

        preg_match("/{$fileOriginaleName}.([0-9]+){$this->suffix}/", $fileName, $output);

        return $output[1] ?? null;
    }
}
