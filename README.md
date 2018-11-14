Binn
====

PHP Class for serialize to binary string.

Original Binn Library for C++ - https://github.com/liteserver/binn

Binn Specification: https://github.com/liteserver/binn/blob/master/spec.md

## Examples

### Simple example

```php
$array = [4, -8875, 'text'];

$binn = new BinnList();

// \xE0\x0F\x03\x20\x04\x41\xDD\x55\xA0\x04text\x00
$serialized = $binn->serialize($array);

```

```php
$binnString = "\xE0\x0F\x03\x20\x04\x41\xDD\x55\xA0\x04text\x00";

$binn = new BinnList();
$unserialized = $binn->unserialize($binnString);

/*
Array
(
    [0] => 4
    [1] => -8875
    [2] => text
)
*/
print_r($unserialized);

```

### Original C++ library style
```php
$binn = new BinnList();
$binn->addUint8(4);
$binn->addInt16(-8875);
$binn->addStr('text');

$serialized = $binn->serialize(); // \xE0\x0F\x03\x20\x04\x41\xDD\x55\xA0\x04text\x00

```

### Nested arrays

```php
$array = [2, true, [123, -456, 789]];

$binn = new BinnList();

// \xE0\x11\x03\x20\x02\x01\xE0\x0B\x03\x20\x7B\x41\xFE\x38\x40\x03\x15
$serialized = $binn->serialize($array);

```