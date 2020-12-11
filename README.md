
# PHP class to logs rotation
PHP Class to rotate log files

This class permit log rotating with diferetne processor.

![tests](https://github.com/cesargb/php-log-rotation/workflows/tests/badge.svg)

## Usage

This is an example:

```php
use use Cesargb\Log\Rotation;

$fileLog='file.log';

$rotation = new Rotation();

$rotation->addProcessor(new GzProcessor());

$rotation->addProcessor(
    (new RotativeProcessor())->setMaxFiles(7)
);

$rotation->rotate($fileLog);
```

## Processor

After of move the content of current log file, you can process changes in
the file was rotated.

### GzProcessor

This processor permit compress in gz format the file rotated.

### RotativeProcessor

This processor permit rotative each file in format file.log.1, file.log.2, ...

You can call method `setMaxFiles` to set the number max of the files rotated.
By default is 366 (One year if rotate each day).

## Todo

* Processor Prefix; To add prefix to file rotated, sample: date (yyyy-mm-dd)
* Processor Archive; To move the file rotated to other dir.
* Processors to move to the cloud

## Test
Run test with:

```bash
composer test
```

## Contributing

Any contributions are welcome.
