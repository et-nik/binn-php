<?php

use PHPUnit\Framework\TestCase;
use \Knik\Binn\BinnObject;
use \Knik\Binn\Binn;

/**
 * @covers Knik\Binn\BinnObject<extended>
 */
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

        $binn = new BinnObject($binnString);
        $arr = $binn->unserialize();
        $this->assertEquals(['hello' => 'world'], $arr);
        $this->assertEquals($binnString, $binn->serialize());
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
        $this->assertEquals([ ["id" => 1, "name" => "John"], ["id" => 2, "name" => "Eric"] ], $arr);
    }

    public function testValidArray()
    {
        $this->assertFalse(BinnObject::validArray([0, 1, 2]));
        $this->assertFalse(BinnObject::validArray([1 => 0, 2 => 2]));
        $this->assertTrue(BinnObject::validArray(['key' => 'val']));
    }
}