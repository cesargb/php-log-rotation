
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

$fileLog='file.log';

$rotation = new Rotation();

$rotation
    ->compress()
    ->files(30)
    ->minSize(1)
    ->rotate($fileLog);
```

## Test
Run test with:

```bash
composer test
```

## Contributing

Any contributions are welcome.
