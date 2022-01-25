<?php

function app_path(string $path = '/'): string
{
    return dirname(__FILE__).$path;
}

$n = 0;

while (true) {
    ++$n;
    file_put_contents(app_path('/file.log'), $n."\n", FILE_APPEND);
}
