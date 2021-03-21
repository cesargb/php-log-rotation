
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

```php
use Cesargb\Log\Rotation;

$rotation = new Rotation();

$rotation
    ->compress() // Optional, compress the file after rotated
    ->files(30) // Optional, files are rotated 30 times before being removed
    ->minSize(1024) // Optional, are rotated when they grow bigger than 1024 bytes
    ->rotate('file.log');
```

## Test
Run test with:

```bash
composer test
```

## Contributing

Any contributions are welcome.
