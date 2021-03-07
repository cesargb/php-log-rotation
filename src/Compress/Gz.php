<?php

namespace Cesargb\Log\Compress;

class Gz
{
    const EXTENSION_COMPRESS = 'gz';

    public function handler($file): ?string
    {
        $fileCompress = $file.'.'.self::EXTENSION_COMPRESS;

        $fd = fopen($file, 'r');

        if (!$fd) {
            return null;
        }

        $gz = gzopen($fileCompress, 'wb');

        if (! $gz) {
            fclose($fd);

            return null;
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
