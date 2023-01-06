
# PHP class to logs rotation

This PHP package allows you to rotate a log file and compress it.

[![tests](https://github.com/cesargb/php-log-rotation/workflows/tests/badge.svg)](https://github.com/cesargb/php-log-rotation/actions)
[![phpstan](https://github.com/cesargb/php-log-rotation/actions/workflows/phpstan.yml/badge.svg)](https://github.com/cesargb/php-log-rotation/actions/workflows/phpstan.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/cesargb/php-log-rotation.svg?style=flat-square&color=brightgreen)](https://packagist.org/packages/cesargb/php-log-rotation)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/cesargb/php-log-rotation/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/cesargb/php-log-rotation/?branch=master)

Note: If you have the version 1 installed, [read this](https://github.com/cesargb/php-log-rotation/tree/v1).

## Installation

You can install this package via composer using:

```bash
composer require cesargb/php-log-rotation
```

## Usage

```php
use Cesargb\Log\Rotation;
use Cesargb\Log\Exceptions\RotationFailed;

$rotation = new Rotation();

$rotation
    ->compress() // Optional, compress the file after rotated. Accept level compression argument.
    ->files(30) // Optional, files are rotated 30 times before being removed. Default 366
    ->minSize(1024) // Optional, are rotated when they grow bigger than 1024 bytes. Default 0
    ->truncate() // Optional, truncate the original log file in place after creating a copy, instead of moving the old log file.
    ->then(function ($filenameTarget, $filenameRotated) {}) // Optional, to get filename target and original filename
    ->catch(function (RotationFailed $exception) {}) // Optional, to catch a exception in rotating
    ->finally(function ($message, $filenameTarget) {}) // Optional, this method will be called when the process has finished
    ->rotate('file.log');
```

Or you can define the options in the constructor

```php
use Cesargb\Log\Rotation;
use Cesargb\Log\Exceptions\RotationFailed;

$rotation = new Rotation([
    'files' => 1,
    'compress' => true, // Set level compression or true to default level. Default false
    'min-size' => 10,
    'truncate' => false,
    'then' => function ($filename) {},
    'catch' => function (RotationFailed $exception) {},
    'finally' => function ($message, $filename) {},
]);

$rotation->rotate('file.log');
```

## Test

Run test with:

```bash
composer test
```

## Contributing

Any contributions are welcome.
