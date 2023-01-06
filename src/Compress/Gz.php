<?php

namespace Cesargb\Log\Compress;

use Exception;

class Gz
{
    public const EXTENSION_COMPRESS = 'gz';

    public function handler(string $filename, ?int $level = null): string
    {
        $filenameCompress = $filename.'.'.self::EXTENSION_COMPRESS;

        $fd = fopen($filename, 'r');

        if ($fd === false) {
            throw new Exception("file {$filename} not can read.", 100);
        }

        $level = $level ?? '';

        $gz = gzopen($filenameCompress, "wb{$level}");

        if ($gz === false) {
            fclose($fd);

            throw new Exception("file {$filenameCompress} not can open.", 101);
        }

        while (!feof($fd)) {
            $data = fread($fd, 1024 * 512);

            $data = $data === false ? '' : $data;

            gzwrite($gz, $data);
        }

        gzclose($gz);
        fclose($fd);
        unlink($filename);

        return $filenameCompress;
    }
}
