<?php

namespace Knik\Binn\Tests\Unit;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use \Knik\Binn\BinnObject;
use \Knik\Binn\Binn;

class BinnObjectTest extends TestCase
{
    public function testObject()
    {
        // https://github.com/liteserver/binn/blob/master/spec.md#a-json-data-such-as-helloworld-is-serialized-as
        /*
            {"hello":"world"}

            \xE2           // [type] object (container)
            \x11           // [size] container total size
            \x01           // [count] key/value pairs
            \x05hello      // key
            \xA0           // [type] = string
            \x05           // [size]
            world\x00      // [data] (null terminated)

        */

        $binnString = "\xE2\x11\x01\x05hello\xA0\x05world\x00";

        $binn = new BinnObject();
        $binn->binnOpen($binnString);
        $arr = $binn->unserialize();
        Assert::assertEquals(['hello' => 'world'], $arr);
        Assert::assertEquals($binnString, $binn->serialize());
    }

    public function testListObjects()
    {
        // https://github.com/liteserver/binn/blob/master/spec.md#a-list-of-objects
        /*
            [ {"id": 1, "name": "John"}, {"id": 2, "name": "Eric"} ]

            \xE0           // [type] list (container)
            \x2B           // [size] container total size
            \x02           // [count] items

            \xE2           // [type] object (container)
            \x14           // [size] container total size
            \x02           // [count] key/value pairs

            \x02id         // key
            \x20           // [type] = uint8
            \x01           // [data] (1)

            \x04name       // key
            \xA0           // [type] = string
            \x04           // [size]
            John\x00       // [data] (null terminated)

            \xE2           // [type] object (container)
            \x14           // [size] container total size
            \x02           // [count] key/value pairs

            \x02id         // key
            \x20           // [type] = uint8
            \x02           // [data] (2)

            \x04name       // key
            \xA0           // [type] = string
            \x04           // [size]
            Eric\x00       // [data] (null terminated)
        */

        $binnString = "\xE0\x2B\x02\xE2\x14\x02\x02id\x20\x01\x04name\xA0\x04John\x00\xE2\x14\x02\x02id\x20\x02\x04name\xA0\x04Eric\x00";

        $binn = new Binn();

        $arr = $binn->unserialize($binnString);
        Assert::assertEquals([ ["id" => 1, "name" => "John"], ["id" => 2, "name" => "Eric"] ], $arr);
    }

    public function testObjectOpen()
    {
        $binnString = "\xE2\x11\x01\x05hello\xA0\x05world\x00";

        $binn = new BinnObject();
        $binn->binnOpen($binnString);
        Assert::assertEquals(['hello' => 'world'], $binn->unserialize());
    }

    public function testSerialize()
    {
        $array = ['hello' => 'world'];
        $binn = new BinnObject();
        $serialized = $binn->serialize($array);

        Assert::assertEquals("\xE2\x11\x01\x05hello\xA0\x05world\x00", $serialized);
    }

    public function testSerializeContainers()
    {
        $binn = new BinnObject();

        $array = ['test' => ['list', 'array'], 'test2' => [1 => 'map', 5 => 'array']];
        $serialized = $binn->serialize($array);
        $unserizlized = $binn->unserialize($serialized);

        Assert::assertEquals($array, $unserizlized);
    }
}
