<?php

use Cesargb\Log\Rotation;

function app_path(string $path = '/'): string
{
    return dirname(__FILE__).$path;
}

function clean()
{
    $files = glob(app_path('/file.log*'));

    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
}

function writeLogInBackground(): string
{
    $pid = exec('php '.app_path('/writelog.php').' > /dev/null 2>&1 & echo $!');

    sleep(1);

    return $pid;
}

include app_path('/../../vendor/autoload.php');

clean();

$pid = writeLogInBackground();
posix_kill($pid, SIGKILL);

$pid = writeLogInBackground();

$rotation = new Rotation();

if (!$rotation->compress()->rotate(app_path('/file.log'))) {
    echo "Failed rotation\n";
}

sleep(1);

posix_kill($pid, SIGKILL);

$lastSave = exec('tail -n 1 '.app_path('/file.log.1'));
$firstNew = exec('head -n 1 '.app_path('/file.log'));

clean();

$dataLost = $firstNew - ($lastSave + 1);

if ($dataLost === 0) {
    echo "OK, no data lossed\n";

    exit(0);
} else {
    echo "Warning, $dataLost data lossed\n";
    exit($dataLost);
}
