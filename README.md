Binn
====

[![Build Status](https://travis-ci.com/et-nik/binn-php.svg?branch=master)](https://travis-ci.org/et-nik/binn-php)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/et-nik/binn-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/et-nik/binn-php/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/et-nik/binn-php/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/et-nik/binn-php/?branch=master)

PHP Class for serialize to binary string.

Original C Binn Library: https://github.com/liteserver/binn

Binn Specification: https://github.com/liteserver/binn/blob/master/spec.md

## Installation

```bash
composer require knik/binn
```

## Examples

### Binn

Sequential arrays:

```php
use Knik\Binn\Binn;

$binn = new Binn();

// List
$array = [123, -456, 789];
$binnString = $binn->serialize($array);
$unserialized = $binn->unserialize($binnString); // Equal with $array
```

Numeric keys array:
```php
$binn = new Binn();

// Map
$array = [1 => "add", 2 => [-12345, 6789]];
$binnString = $binn->serialize($array);
$unserialized = $binn->unserialize($binnString); // Equal with $array
```

String keys array:
```php
$binn = new Binn();

// Object
$array = ["hello" => "world"];
$binnString = $binn->serialize($array);
$unserialized = $binn->unserialize($binnString); // Equal with $array
```

Mixed arrays:

```php
$binn = new Binn();
$array = [ ["id" => 1, "name" => "John"], ["id" => 2, "name" => "Eric"] ]

// A list of objects
$binnString = $binn->serialize($array);
$unserialized = $binn->unserialize($binnString); // Equal with $array
```

Blob:
```php
$binn = new Binn();
$file = fopen('/path/to/file.jpg', 'rb');

// Filedata in binn structure
$bin1 = $binn->serialize($file);

// Filedata in binn list structure
$bin2 = $binn->serialize(['file' => $file]);
```

### Symfony Serializer

You can use BinnEncoder with Symfony Serializer

```php
use Knik\Binn\Encoder\BinnEncoder;
use Symfony\Component\Serializer\Serializer;

$encoders = [new BinnEncoder()];
$serializer = new Serializer([], $encoders);

$serializer->serialize("\x40\xD0\x06", 'binn');
```

### Original C library style
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
