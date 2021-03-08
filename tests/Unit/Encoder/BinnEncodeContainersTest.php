<?php

namespace Knik\Binn\Tests\Unit\Encoder;

use Knik\Binn\Tests\BinnTestCase;
use PHPUnit\Framework\Assert;

class BinnEncodeContainersTest extends BinnTestCase
{
    // https://github.com/liteserver/binn/blob/master/spec.md#a-list-of-3-integers
    public function testListEncode(): void
    {
        $encoder = $this->getEncoder();

        $result = $encoder->encode([123, -456, 789], 'binn');

        Assert::assertEquals("\xE0\x0B\x03\x20\x7B\x41\xFE\x38\x40\x03\x15", $result);
    }

    public function testStringsListEncode(): void
    {
        $encoder = $this->getEncoder();

        $result = $encoder->encode(['Hello', " World!"], 'binn');

        Assert::assertEquals(
            "\xE0"
            . "\x15"
            . "\x02"
            . "\xA0"
            . "\x05"
            . "Hello\x00"
            . "\xA0"
            . "\x07"
            . " World!\x00",
            $result
        );
    }

    // https://github.com/liteserver/binn/blob/master/spec.md#a-list-inside-a-map
    public function testListInsideMapEncode(): void
    {
        $encoder = $this->getEncoder();

        $result = $encoder->encode([
            1 => 'add',
            2 => [-12345, 6789]
        ], 'binn');

        Assert::assertEquals(
            "\xE1"              // [type] map (container)
                . "\x1A"                 // [size] container total size
                . "\x02"                 // [count] key/value pairs

                . "\x00\x00\x00\x01"     // key
                    . "\xA0"                    // [type] = string
                    . "\x03"                    // [size]
                    . "add\x00"                 // [data] (null terminated)

                . "\x00\x00\x00\x02"     // key
                    . "\xE0"                    // [type] list (container)
                    . "\x09"                    // [size] container total size
                    . "\x02"                    // [count] items
                    . "\x41"                    // [type] = int16
                    . "\xCF\xC7"                // [data] (-12345)
                    . "\x40"                    // [type] = uint16
                    . "\x1A\x85",               // [data] (6789)
            $result
        );
    }

    // https://github.com/liteserver/binn/blob/master/spec.md#a-list-of-objects
    public function testListObjects(): void
    {
        $encoder = $this->getEncoder();

        $result = $encoder->encode([
            ["id" => 1, "name" => "John"],
            ["id" => 2, "name" => "Eric"]
        ], 'binn');

        Assert::assertEquals(
        "\xE0"                 // [type] list (container)
                . "\x2B"                // [size] container total size
                . "\x02"                // [count] items

                    . "\xE2"                // [type] object (container)
                    . "\x14"                // [size] container total size
                    . "\x02"                // [count] key/value pairs

                        . "\x02id"              // key
                        . "\x20"                // [type] = uint8
                        . "\x01"                // [data] (1)

                        . "\x04name"            // key
                        . "\xA0"                // [type] = string
                        . "\x04"                // [size]
                        . "John\x00"            // [data] (null terminated)

                    . "\xE2"                // [type] object (container)
                    . "\x14"                // [size] container total size
                    . "\x02"                // [count] key/value pairs

                        . "\x02id"              // key
                        . "\x20"                // [type] = uint8
                        . "\x02"                // [data] (2)

                        . "\x04name"            // key
                        . "\xA0"                // [type] = string
                        . "\x04Eric"            // [size]
                        . "\x00",               // [data] (null terminated)
            $result
        );
    }
}
