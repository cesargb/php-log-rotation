<?php

namespace Cesargb\Log\Compress;

use Exception;

class Gz
{
    const EXTENSION_COMPRESS = 'gz';

    public function handler($file): string
    {
        $fileCompress = $file.'.'.self::EXTENSION_COMPRESS;

        $fd = fopen($file, 'r');

        if (!$fd) {
            throw new Exception("file {$file} not can read.", 100);
        }

        $gz = gzopen($fileCompress, 'wb');

        if (! $gz) {
            fclose($fd);

            throw new Exception("file {$fileCompress} not can open.", 101);
        }

        while (! feof($fd)) {
            gzwrite($gz, fread($fd, 1024 * 512));
        }

        gzclose($gz);
        fclose($fd);
        unlink($file);

        return $fileCompress;
    }
}
