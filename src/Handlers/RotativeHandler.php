<?php

namespace Cesargb\Log\Handlers;

class RotativeHandler extends AbstractHandler
{
    const EXTENSION_COMPRESS = 'gz';

    protected $max_files;

    protected $dir_to_archive;

    public function __construct($file, $max_files = 366, bool $compress = true, $dir_to_archive = null)
    {
        parent::__construct($file, $compress);

        $this->max_files = $max_files;

        if (empty($dir_to_archive)) {
            $this->dir_to_archive = dirname($file);
        } else {
            if (substr($dir_to_archive, 0, 1) == DIRECTORY_SEPARATOR) {
                $this->dir_to_archive = $dir_to_archive;
            } else {
                $this->dir_to_archive = dirname($file).DIRECTORY_SEPARATOR.$dir_to_archive;
            }
        }
    }

    public function run()
    {
        $this->file_rotated = $this->dir_to_archive.DIRECTORY_SEPARATOR.basename($this->getRotatedFileName());

        $result = $this->rotate();

        if ($result instanceof Exception) {
            throw $e;
        } else {
            return $result;
        }
    }

    protected function rotate()
    {
        if ($this->compress) {
            $file_tmp_name = tempnam(dirname($this->file), basename($this->file));

            if ($this->moveTo($file_tmp_name)) {
                $fd_tmp = fopen($file_tmp_name, 'r');

                if ($fd_tmp) {
                    $fd_compress = gzopen($this->file_rotated, 'w');

                    while (! feof($fd_tmp)) {
                        gzwrite($fd_compress, fread($fd_tmp, 1024 * 512));
                    }

                    gzclose($fd_compress);

                    fclose($fd_tmp);

                    unlink($file_tmp_name);

                    return true;
                }
            }

            return false;
        } else {
            return $this->moveTo($this->file_rotated);
        }
    }

    protected function getRotatedFileName()
    {
        $fileInfo = pathinfo($this->file);

        $glob = $fileInfo['dirname'].DIRECTORY_SEPARATOR.$fileInfo['filename'];

        if (! empty($fileInfo['extension'])) {
            $glob .= '.'.$fileInfo['extension'];
        }

        $glob .= '.*';

        if ($this->compress) {
            $glob .= '.'.self::EXTENSION_COMPRESS;
        }

        $curFiles = glob($glob);

        for ($n = count($curFiles); $n > 0; $n--) {
            $file_to_move = str_replace('*', $n, $glob);

            if (file_exists($file_to_move)) {
                if ($this->max_files > 0 && $n >= $this->max_files) {
                    unlink($file_to_move);
                } else {
                    rename($file_to_move, str_replace('*', $n + 1, $glob));
                }
            }
        }

        return str_replace('*', '1', $glob);
    }
}
