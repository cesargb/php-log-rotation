
# PHP class to logs rotation
PHP Class to rotate log files

This class permit log rotating with diferetne processor.

[![tests](https://github.com/cesargb/php-log-rotation/workflows/tests/badge.svg)](https://github.com/cesargb/php-log-rotation/actions)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/cesargb/php-log-rotation.svg?style=flat-square&color=brightgreen)](https://packagist.org/packages/cesargb/php-log-rotation)

Note: If you have the version 1 installed, [read this](https://github.com/cesargb/php-log-rotation/tree/v1).

## Installation

You can install this package via composer using:

```bash
composer require cesargb/php-log-rotation
```

## Usage

```php
use Cesargb\Log\Rotation;

$rotation = new Rotation();

$rotation
    ->compress() // Optional, compress the file after rotated. Default false
    ->files(30) // Optional, files are rotated 30 times before being removed. Default 366
    ->minSize(1024) // Optional, are rotated when they grow bigger than 1024 bytes. Default 0
    ->then(function ($filename) {}) // Optional, to get filename rotated
    ->catch(function ($exception) {}) // Optional, to catch a exception in rotating
    ->rotate('file.log');
```

Or you can define the options in the constructor

```php
use Cesargb\Log\Rotation;

$rotation = new Rotation([
    'files' => 1,
    'compress' => true,
    'min-size' => 10,
    'then' => function ($filename) {},
    'catch' => function ($error) {},
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
