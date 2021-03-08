
# PHP class to logs rotation
PHP Class to rotate log files

This class permit log rotating with diferetne processor.

[![tests](https://github.com/cesargb/php-log-rotation/workflows/tests/badge.svg)](https://github.com/cesargb/php-log-rotation/actions)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/cesargb/php-log-rotation.svg?style=flat-square&color=brightgreen)](https://packagist.org/packages/cesargb/php-log-rotation)

## Installation

You can install this package via composer using:

```bash
composer require cesargb/php-log-rotation
```

## Usage

This is an example:

```php
use Cesargb\Log\Rotation;

$rotation = new Rotation();

// Rotate a file
$rotation->rotate('file.log');

// Log files are rotated 10 times before being removed, 366 default
$rotation->files(30)->rotate('file.log');

// Compress file rotated
$rotation->compress()->rotate('file.log');

// Log files are rotated only if they grow bigger then 1024 bytes
$rotation->minSize(1024)->rotate('file.log');
```

## Test
Run test with:

```bash
composer test
```

## Contributing

Any contributions are welcome.
