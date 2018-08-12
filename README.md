
# PHP class to logs rotation
PHP Class to Rotate files with compression

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
