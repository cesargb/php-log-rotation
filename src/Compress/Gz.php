<?php

namespace Cesargb\Log\Compress;

use Exception;

class Gz
{
    public const EXTENSION_COMPRESS = 'gz';

    public function handler(string $filename): string
    {
        $filenameCompress = $filename.'.'.self::EXTENSION_COMPRESS;

        $fd = fopen($filename, 'r');

        if ($fd === false) {
            throw new Exception("file {$filename} not can read.", 100);
        }

        $gz = gzopen($filenameCompress, 'wb');

        if ($gz === false) {
            fclose($fd);

            throw new Exception("file {$filenameCompress} not can open.", 101);
        }

        while (!feof($fd)) {
            gzwrite($gz, fread($fd, 1024 * 512));
        }

        gzclose($gz);
        fclose($fd);
        unlink($filename);

        return $filenameCompress;
    }
}
