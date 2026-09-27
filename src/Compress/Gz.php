<?php

namespace Cesargb\Log\Compress;

use Cesargb\Log\Results\ProcessResult;

class Gz
{
    public const EXTENSION_COMPRESS = 'gz';

    public function handler(string $filename, ?int $level = null): ProcessResult
    {
        $filenameCompress = $filename.'.'.self::EXTENSION_COMPRESS;

        $fd = fopen($filename, 'r');

        if ($fd === false) {
            return ProcessResult::failed($filename, "file {$filename} not can read.");
        }

        $level = $level ?? '';

        $gz = gzopen($filenameCompress, "wb{$level}");

        if ($gz === false) {
            fclose($fd);

            return ProcessResult::failed($filename, "file {$filenameCompress} not can open.");
        }

        while (! feof($fd)) {
            $data = fread($fd, 1024 * 512);

            if ($data === false) {
                return $this->abortCompression($gz, $fd, $filenameCompress, $filename, "file {$filename} not can read.");
            }

            $bytesWritten = gzwrite($gz, $data);

            if ($bytesWritten !== strlen($data)) {
                return $this->abortCompression($gz, $fd, $filenameCompress, $filename, "file {$filenameCompress} not can write.");
            }
        }

        if (! gzclose($gz)) {
            fclose($fd);
            @unlink($filenameCompress);

            return ProcessResult::failed($filename, "file {$filenameCompress} not can close.");
        }

        fclose($fd);
        unlink($filename);

        return ProcessResult::successful($filename, $filenameCompress);
    }

    /**
     * @param  resource  $gz
     * @param  resource  $fd
     */
    private function abortCompression($gz, $fd, string $filenameCompress, string $filename, string $error): ProcessResult
    {
        gzclose($gz);
        fclose($fd);
        @unlink($filenameCompress);

        return ProcessResult::failed($filename, $error);
    }
}
