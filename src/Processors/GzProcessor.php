<?php

namespace Cesargb\Log\Processors;

class GzProcessor extends AbstractProcessor
{
    const EXTENSION_COMPRESS = 'gz';

    public function handler($file)
    {
        parent::handler($file);

        $nextFile = $this->fileOriginal.'.'.self::EXTENSION_COMPRESS;

        $fd = fopen($file, 'r');

        if ($fd) {
            $gz = gzopen($nextFile, 'wb');

            if (! $gz) {
                fclose($fd);

                return false;
            }

            while (! feof($fd)) {
                gzwrite($gz, fread($fd, 1024 * 512));
            }

            gzclose($gz);

            fclose($fd);

            unlink($file);

            return $this->processed($nextFile);
        } else {
            return false;
        }
    }
}
