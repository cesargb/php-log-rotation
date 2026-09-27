<?php

namespace Cesargb\Log\Compress;

use Cesargb\Log\Exceptions\ProcessException;
use Cesargb\Log\Results\ProcessResult;

class Gz
{
    public const EXTENSION_COMPRESS = 'gz';

    /**
     * @deprecated Use process() instead. handler() will be removed in a future major version.
     *
     * @throws ProcessException
     */
    public function handler(string $filename, ?int $level = null): string
    {
        $result = $this->process($filename, $level);

        if ($result->exception !== null) {
            throw $result->exception;
        }

        return $result->filenameTarget ?? throw new ProcessException("file {$filename} not can compress.", 100);
    }

    public function process(string $filename, ?int $level = null): ProcessResult
    {
        $filenameCompress = $filename.'.'.self::EXTENSION_COMPRESS;

        $fd = fopen($filename, 'r');

        if ($fd === false) {
            return ProcessResult::failed($filename, new ProcessException("file {$filename} not can read.", 100));
        }

        $level = $level ?? '';

        $gz = gzopen($filenameCompress, "wb{$level}");

        if ($gz === false) {
            fclose($fd);

            return ProcessResult::failed($filename, new ProcessException("file {$filenameCompress} not can open.", 101));
        }

        while (! feof($fd)) {
            $data = fread($fd, 1024 * 512);

            if ($data === false) {
                return $this->abortCompression($gz, $fd, $filenameCompress, $filename, new ProcessException("file {$filename} not can read.", 102));
            }

            $bytesWritten = gzwrite($gz, $data);

            if ($bytesWritten !== strlen($data)) {
                return $this->abortCompression($gz, $fd, $filenameCompress, $filename, new ProcessException("file {$filenameCompress} not can write.", 103));
            }
        }

        if (! gzclose($gz)) {
            fclose($fd);
            @unlink($filenameCompress);

            return ProcessResult::failed($filename, new ProcessException("file {$filenameCompress} not can close.", 104));
        }

        fclose($fd);
        unlink($filename);

        return ProcessResult::successful($filename, $filenameCompress);
    }

    /**
     * @param  resource  $gz
     * @param  resource  $fd
     */
    private function abortCompression($gz, $fd, string $filenameCompress, string $filename, ProcessException $exception): ProcessResult
    {
        gzclose($gz);
        fclose($fd);
        @unlink($filenameCompress);

        return ProcessResult::failed($filename, $exception);
    }
}
