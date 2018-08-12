<?php

namespace Cesargb\Log\Processors;

class RotativeProcessor extends AbstractProcessor
{
    protected $maxFiles = 366;

    public function setMaxFiles($maxFiles)
    {
        $this->maxFiles = $maxFiles;

        return $this;
    }

    public function handler($file)
    {
        parent::handler($file);

        $fileInfo = pathinfo($file);

        $extension_in = $fileInfo['extension'] ?? '';

        $fileInfo = pathinfo($this->fileOriginal);

        $extension_original = $fileInfo['extension'] ?? '';

        $glob = $fileInfo['dirname'].DIRECTORY_SEPARATOR.$fileInfo['filename'];

        if (! empty($fileInfo['extension'])) {
            $glob .= '.'.$fileInfo['extension'];
        }

        if ($extension_in != '' && $extension_in != $extension_original) {
            $glob .= '.'.$extension_in;
        }

        $glob .= '.*';

        $curFiles = glob($glob);

        for ($n = count($curFiles); $n > 0; $n--) {
            $file_to_move = str_replace('*', $n, $glob);

            if (file_exists($file_to_move)) {
                if ($this->maxFiles > 0 && $n >= $this->maxFiles) {
                    unlink($file_to_move);
                } else {
                    rename($file_to_move, str_replace('*', $n + 1, $glob));
                }
            }
        }

        $nextFile = str_replace('*', '1', $glob);

        rename($file, $nextFile);

        return $this->processed($nextFile);
    }
}
